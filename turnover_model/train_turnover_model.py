"""
Train the employee turnover (resignation) risk model.

    p = 1 / (1 + e^-z),   z = b0 + sum_j bj * (xj - mean_j) / std_j

Ridge-regularised logistic regression, fitted by Newton's method on class-weighted
log-loss, validated on a later time period than it was trained on.

Needs only numpy and pandas.

    python3 train_turnover_model.py                      # synthetic data next to this script
    python3 train_turnover_model.py --train my_export.csv --score current.csv

Inputs
  --train   one row per employee per snapshot, with LABEL (1 = resigned within the horizon)
            and EXIT_TYPE (blank, or the type of any exit within the horizon)
  --score   the same columns without the label: the employees to score now

Outputs (in --out-dir)
  turnover_model.json   everything needed to score in PHP: medians, means, stds, weights, risk bands
  risk_scores.csv       current employees, highest risk first, with their top reasons
  training_report.txt   the evaluation printed below
"""
import argparse
import json
from datetime import datetime, timezone
from pathlib import Path

import numpy as np
import pandas as pd

# ----- What the data looks like ------------------------------------------------------------
DATE = "snapshot_date"
ID = "employee_id"
LABEL = "resigned_within_6m"
EXIT_TYPE = "exit_type_within_6m"
NUMERIC = [
    "grade", "tenure_months", "months_since_promotion", "compa_ratio", "manager_changed_6m",
    "team_exits_6m", "avg_rating", "rating_change", "self_vs_manager_rating_gap", "tasks_vs_dept_avg",
    "late_task_pct", "projects_assigned", "sick_days_q", "training_hours_12m", "days_since_last_login",
]
CATEGORICAL = ["department", "role", "contract_type"]

# How each signal reads in a manager-facing reason
REASONS = {
    "grade": lambda v: f"Grade {v:.0f}",
    "tenure_months": lambda v: f"{v:.0f} months with the organisation",
    "months_since_promotion": lambda v: f"{v:.0f} months since last promotion",
    "compa_ratio": lambda v: f"Paid at {v:.0%} of pay-band midpoint",
    "manager_changed_6m": lambda v: "Manager changed in the last 6 months" if v >= 0.5 else "No recent manager change",
    "team_exits_6m": lambda v: f"{v:.0f} colleagues left the department in 6 months",
    "avg_rating": lambda v: f"Average rating {v:.0f}",
    "rating_change": lambda v: f"Rating {v:+.1f} vs last quarter",
    "self_vs_manager_rating_gap": lambda v: f"Rates self {v:.0f} points above assessors",
    "tasks_vs_dept_avg": lambda v: f"Logs {v:.2f}x the department's average tasks",
    "late_task_pct": lambda v: f"{v:.0%} of tasks logged late",
    "projects_assigned": lambda v: f"{v:.0f} projects assigned",
    "sick_days_q": lambda v: f"{v:.0f} sick days this quarter",
    "training_hours_12m": lambda v: f"{v:.0f} training hours in 12 months",
    "days_since_last_login": lambda v: f"{v:.0f} days since last sign-in",
}

LAMBDAS = [0.001, 0.003, 0.01, 0.03, 0.1, 0.3, 1.0, 3.0]
MAX_MISSING_SHARE = 0.4   # drop a signal that is mostly blank in the training period


# ----- Preparing inputs -------------------------------------------------------------------
class Preprocessor:
    """Median-fill (+ missing flag), one-hot categories, standardise. Fitted on training rows only."""

    def fit(self, df, numeric, categorical):
        self.numeric = list(numeric)
        self.medians = {c: float(df[c].median()) for c in self.numeric}
        self.missing_flags = [c for c in self.numeric if df[c].isna().any()]
        self.levels = {c: sorted(df[c].dropna().astype(str).unique()) for c in categorical}
        raw = self._raw(df)
        self.columns = list(raw.columns)
        self.means = raw.mean()
        self.stds = raw.std(ddof=0).replace(0, 1.0)
        return self

    def _raw(self, df):
        out = {}
        for c in self.numeric:
            out[c] = df[c].fillna(self.medians[c]).astype(float)
        for c in self.missing_flags:
            out[f"{c}__missing"] = df[c].isna().astype(float)
        for c, levels in self.levels.items():
            for level in levels:
                out[f"{c}={level}"] = (df[c].astype(str) == level).astype(float)
        return pd.DataFrame(out, index=df.index)

    def transform(self, df):
        raw = self._raw(df)[self.columns]
        return ((raw - self.means) / self.stds).to_numpy()


# ----- The model --------------------------------------------------------------------------
def sigmoid(z):
    return 1.0 / (1.0 + np.exp(-np.clip(z, -35, 35)))


def fit_logistic(X, y, lam, positive_weight):
    """
    Minimise  -(1/S) * sum_i s_i [y log p + (1-y) log(1-p)]  +  lam * sum_{j>=1} b_j^2
    with s_i = positive_weight for leavers, 1 for stayers, by Newton's method:
        grad = (1/S) X'(s*(p-y)) + 2*lam*b      hess = (1/S) X' diag(s*p*(1-p)) X + 2*lam*I
    The intercept is not penalised.
    """
    X1 = np.c_[np.ones(len(X)), X]
    s = np.where(y == 1, positive_weight, 1.0)
    S = s.sum()
    penalty = np.full(X1.shape[1], 2 * lam)
    penalty[0] = 0.0
    b = np.zeros(X1.shape[1])
    for _ in range(100):
        p = sigmoid(X1 @ b)
        grad = X1.T @ (s * (p - y)) / S + penalty * b
        hess = (X1.T * (s * p * (1 - p))) @ X1 / S + np.diag(penalty) + 1e-9 * np.eye(len(b))
        step = np.linalg.solve(hess, grad)
        b -= step
        if np.abs(step).max() < 1e-8:
            break
    # Weighting leavers by w adds log(w) to every fitted log-odds; take it back off so
    # the output is a real probability again (prior correction).
    b[0] -= np.log(positive_weight)
    return b


def predict(b, X):
    return sigmoid(b[0] + X @ b[1:])


def class_weight(y, mode):
    return (len(y) - y.sum()) / max(y.sum(), 1) if mode == "balanced" else 1.0


# ----- Measuring it ---------------------------------------------------------------------
def auc(score, y):
    """Chance a random leaver scores above a random stayer (ties count half)."""
    ranks = pd.Series(score).rank().to_numpy()
    pos = y == 1
    n_pos, n_neg = pos.sum(), (~pos).sum()
    if n_pos == 0 or n_neg == 0:
        return np.nan
    return (ranks[pos].sum() - n_pos * (n_pos + 1) / 2) / (n_pos * n_neg)


def precision_at(score, y, share):
    k = max(1, int(round(share * len(score))))
    return y[np.argsort(-score, kind="stable")[:k]].mean()


def log_loss(p, y):
    p = np.clip(p, 1e-12, 1 - 1e-12)
    return float(-np.mean(y * np.log(p) + (1 - y) * np.log(1 - p)))


# ----- Time-based splitting ---------------------------------------------------------------
def purged_split(dates, first_test_date, horizon):
    """Train rows must end `horizon` before the test period, so their labels can't see into it."""
    train = dates <= first_test_date - horizon
    test = dates >= first_test_date
    return train, test


def choose_lambda(df, numeric, horizon, weight_mode, log):
    """
    Rolling-origin validation inside the training period: train on the past, validate on each
    later quarter. Picks the penalty with the lowest validation log-loss, which rewards both
    good ranking and honest probabilities (AUC alone would happily shrink every weight to ~0).
    """
    dates = pd.to_datetime(df[DATE])
    unique = sorted(dates.unique())
    results = {lam: [] for lam in LAMBDAS}
    aucs = {lam: [] for lam in LAMBDAS}
    for val_date in unique[-3:]:
        fold_train = (dates <= val_date - horizon).to_numpy()
        fold_val = (dates == val_date).to_numpy()
        y_tr, y_val = df[LABEL].to_numpy()[fold_train], df[LABEL].to_numpy()[fold_val]
        if fold_train.sum() == 0 or y_tr.sum() < 5 or y_val.sum() == 0:
            continue
        prep = Preprocessor().fit(df[fold_train], numeric, CATEGORICAL)
        X_tr, X_val = prep.transform(df[fold_train]), prep.transform(df[fold_val])
        for lam in LAMBDAS:
            b = fit_logistic(X_tr, y_tr, lam, class_weight(y_tr, weight_mode))
            p_val = predict(b, X_val)
            results[lam].append(log_loss(p_val, y_val))
            aucs[lam].append(auc(p_val, y_val))
    scored = {lam: np.mean(v) for lam, v in results.items() if v}
    if not scored:
        log("  Not enough history for validation folds; using lambda = 0.03")
        return 0.03
    for lam, loss in scored.items():
        log(f"  lambda {lam:<6} validation log-loss {loss:.4f}   AUC {np.nanmean(aucs[lam]):.3f}  ({len(results[lam])} folds)")
    chosen = min(scored, key=scored.get)
    log(f"  -> lambda = {chosen}")
    return chosen


# ----- Explaining a score ---------------------------------------------------------------
def top_reasons(row, contributions, columns, n=3):
    """Largest pushes toward leaving, in plain language. Skips ones under 15% of this person's biggest."""
    order = np.argsort(-contributions)
    biggest = contributions[order[0]]
    reasons = []
    for j in order:
        if contributions[j] <= 0 or contributions[j] < 0.15 * biggest or len(reasons) == n:
            break
        col = columns[j]
        if col.endswith("__missing"):
            continue
        if "=" in col:
            field, level = col.split("=", 1)
            reasons.append(f"{field.replace('_', ' ').capitalize()}: {level}")
        else:
            value = row[col]
            reasons.append(REASONS.get(col, lambda v, c=col: f"{c} = {v}")(value) if pd.notna(value) else f"{col} unknown")
    return reasons


# ----- Main -----------------------------------------------------------------------------
def main():
    here = Path(__file__).resolve().parent
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--train", default=here / "turnover_train.csv")
    parser.add_argument("--score", default=here / "turnover_score_current.csv")
    parser.add_argument("--out-dir", default=here)
    parser.add_argument("--horizon-days", type=int, default=182)
    parser.add_argument("--test-quarters", type=int, default=3, help="latest snapshots held out for the final test")
    parser.add_argument("--class-weight", choices=["balanced", "none"], default="balanced")
    parser.add_argument("--high-risk-share", type=float, default=0.10, help="share of staff labelled High risk")
    parser.add_argument("--medium-risk-share", type=float, default=0.20, help="next share labelled Medium risk")
    args = parser.parse_args()
    out_dir = Path(args.out_dir)
    horizon = pd.Timedelta(days=args.horizon_days)
    lines = []

    def log(text=""):
        print(text)
        lines.append(text)

    # Step 1: rows and answers -- voluntary leaving only
    data = pd.read_csv(args.train)
    data[EXIT_TYPE] = data[EXIT_TYPE].fillna("")
    involuntary = (data[EXIT_TYPE] != "") & (data[LABEL] == 0)
    data = data[~involuntary].reset_index(drop=True)
    dates = pd.to_datetime(data[DATE])
    log(f"Training data: {len(data)} rows, {data[ID].nunique()} employees, {int(data[LABEL].sum())} resignation rows "
        f"({data[LABEL].mean():.1%}); dropped {int(involuntary.sum())} rows with dismissal/retirement/contract-end exits")

    # Step 3: hold out the latest quarters, with a gap so training labels can't see into them
    unique_dates = sorted(dates.unique())
    first_test = unique_dates[-args.test_quarters]
    train_mask, test_mask = purged_split(dates, first_test, horizon)
    train_df, test_df = data[train_mask.to_numpy()], data[test_mask.to_numpy()]
    log(f"Train: {train_df[DATE].min()} .. {train_df[DATE].max()} ({len(train_df)} rows, {int(train_df[LABEL].sum())} leavers)")
    log(f"Test:  {test_df[DATE].min()} .. {test_df[DATE].max()} ({len(test_df)} rows, {int(test_df[LABEL].sum())} leavers)")

    # Step 2: drop signals that barely exist in the training period
    missing = train_df[NUMERIC].isna().mean()
    numeric = [c for c in NUMERIC if missing[c] <= MAX_MISSING_SHARE]
    for c in NUMERIC:
        if c not in numeric:
            log(f"Not used: {c} is blank in {missing[c]:.0%} of training rows")

    # Step 4: choose the penalty, fit, evaluate on the held-out period
    log("\nChoosing the regularisation strength (rolling validation inside the training period):")
    lam = choose_lambda(train_df, numeric, horizon, args.class_weight, log)

    prep = Preprocessor().fit(train_df, numeric, CATEGORICAL)
    y_train, y_test = train_df[LABEL].to_numpy(), test_df[LABEL].to_numpy()
    b = fit_logistic(prep.transform(train_df), y_train, lam, class_weight(y_train, args.class_weight))
    p_test = predict(b, prep.transform(test_df))

    # Step 5: grade it, and compare against simple alternatives
    base_rate = y_test.mean()
    rule = (test_df["months_since_promotion"] >= 36).astype(float).to_numpy()
    single = {c: auc(test_df[c].fillna(train_df[c].median()).to_numpy(), y_test) for c in numeric}
    single = {c: max(a, 1 - a) for c, a in single.items()}
    best_single = max(single, key=single.get)
    log(f"\nHeld-out test ({test_df[DATE].min()} .. {test_df[DATE].max()}):")
    log(f"  AUC                          {auc(p_test, y_test):.3f}   (random = 0.500)")
    log(f"  Rule 'no promotion in 3 yrs' {auc(rule, y_test):.3f}")
    log(f"  Best single signal           {single[best_single]:.3f}   ({best_single})")
    prec = precision_at(p_test, y_test, args.high_risk_share)
    log(f"  Top {args.high_risk_share:.0%} flagged who resigned  {prec:.1%}  vs {base_rate:.1%} overall  -> lift {prec / base_rate:.1f}x")
    log(f"  Log-loss {log_loss(p_test, y_test):.4f} (always predicting the base rate: {log_loss(np.full_like(p_test, y_train.mean()), y_test):.4f})")
    log(f"  Brier score {np.mean((p_test - y_test) ** 2):.4f}")
    log("  Calibration (does 'x% risk' mean x%?):")
    bins = pd.qcut(p_test, 5, labels=False, duplicates="drop")
    for k in sorted(set(bins)):
        sel = bins == k
        log(f"    predicted {p_test[sel].mean():6.1%}   actual {y_test[sel].mean():6.1%}   ({sel.sum()} rows)")

    # Step 7: refit on every labelled row for the model we actually use
    prep_final = Preprocessor().fit(data, numeric, CATEGORICAL)
    y_all = data[LABEL].to_numpy()
    b_final = fit_logistic(prep_final.transform(data), y_all, lam, class_weight(y_all, args.class_weight))
    p_all = predict(b_final, prep_final.transform(data))
    # For reference: the probability that put someone in the riskiest 10% historically
    historical_high_cut = float(np.quantile(p_all, 1 - args.high_risk_share))

    weights = pd.Series(b_final[1:], index=prep_final.columns)
    log(f"\nFinal model: refitted on all {len(data)} labelled rows, lambda {lam}")
    log("Strongest signals (standardised weight, odds ratio per 1 std):")
    for col, w in weights.sort_values(key=abs, ascending=False).head(12).items():
        log(f"  {col:<34} {w:+.3f}   x{np.exp(w):.2f}  {'raises' if w > 0 else 'lowers'} risk")

    model = {
        "model": "logistic_regression_l2",
        "trained_at": datetime.now(timezone.utc).isoformat(timespec="seconds"),
        "label": f"{LABEL}: resigned within {args.horizon_days} days of the snapshot",
        "training_rows": int(len(data)),
        "training_positives": int(y_all.sum()),
        "lambda": lam,
        "class_weight": args.class_weight,
        "test_metrics": {
            "auc": round(float(auc(p_test, y_test)), 4),
            f"precision_top_{int(args.high_risk_share * 100)}pct": round(float(prec), 4),
            "base_rate": round(float(base_rate), 4),
        },
        "formula": "z = intercept + sum(weight[c] * (x[c] - mean[c]) / std[c]); p = 1 / (1 + exp(-z))",
        "numeric_features": numeric,
        "numeric_medians": prep_final.medians,
        "missing_flag_features": prep_final.missing_flags,
        "categorical_levels": prep_final.levels,
        "columns": prep_final.columns,
        "means": {c: float(v) for c, v in prep_final.means.items()},
        "stds": {c: float(v) for c, v in prep_final.stds.items()},
        "intercept": float(b_final[0]),
        "weights": {c: float(w) for c, w in weights.items()},
        # Bands are ranks among the employees scored together, so High is always a short list
        "risk_bands": {
            "high_share": args.high_risk_share,
            "medium_share": args.medium_risk_share,
            "historical_high_probability": historical_high_cut,
            "historical_mean_probability": float(p_all.mean()),
        },
    }
    (out_dir / "turnover_model.json").write_text(json.dumps(model, indent=2))

    # Step 6: score current employees and explain each score
    current = pd.read_csv(args.score)
    X_now = prep_final.transform(current)
    p_now = predict(b_final, X_now)
    contributions = X_now * b_final[1:]
    raw_now = prep_final._raw(current)
    raw_now[numeric] = current[numeric]   # show real values (blank stays blank) in reasons
    # High = riskiest high_share of the people scored now, Medium = the next medium_share
    rank_share = pd.Series(p_now).rank(ascending=False, method="first").to_numpy() / len(p_now)
    rows = []
    for i in range(len(current)):
        reasons = top_reasons(raw_now.iloc[i], contributions[i], prep_final.columns)
        band = ("High" if rank_share[i] <= args.high_risk_share
                else "Medium" if rank_share[i] <= args.high_risk_share + args.medium_risk_share else "Low")
        rows.append({
            ID: current[ID].iloc[i], "department": current["department"].iloc[i], "role": current["role"].iloc[i],
            "risk_probability": round(float(p_now[i]), 4), "risk_band": band,
            **{f"reason_{k + 1}": reasons[k] if k < len(reasons) else "" for k in range(3)},
        })
    scores = pd.DataFrame(rows).sort_values("risk_probability", ascending=False)
    scores.to_csv(out_dir / "risk_scores.csv", index=False)

    above = (p_now >= historical_high_cut).mean()
    log(f"\nDrift check: average risk now {p_now.mean():.1%} vs {p_all.mean():.1%} in the training history; "
        f"{above:.0%} of current staff exceed the historical top-{args.high_risk_share:.0%} threshold ({historical_high_cut:.1%}).")
    if above > 1.5 * args.high_risk_share:
        log("  Risk is running well above the past. Check for a real change (pay, reorganisation) before trusting individual ranks.")
    log(f"Scored {len(scores)} current employees: "
        + ", ".join(f"{n} {band}" for band, n in scores.risk_band.value_counts().reindex(["High", "Medium", "Low"]).fillna(0).astype(int).items()))
    log("Highest risk:")
    for _, r in scores.head(5).iterrows():
        log(f"  {r[ID]}  {r.department:<16} {r.risk_probability:6.1%}  {r.risk_band:<6}  {r.reason_1}; {r.reason_2}; {r.reason_3}")
    log(f"\nWrote turnover_model.json, risk_scores.csv, training_report.txt to {out_dir}")
    (out_dir / "training_report.txt").write_text("\n".join(lines) + "\n")


if __name__ == "__main__":
    main()
