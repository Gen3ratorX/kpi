"""
Compare candidate models for the turnover risk model on the same time-based split, and
produce the results table and figures for the report.

Every model gets the same treatment:
  - trained on the same rows, tested once on the same later period (with the 6-month gap)
  - tuned the same way: lowest mean validation log-loss on rolling folds inside the
    training period, never on the test period
  - scored with the same metrics, with 95% bootstrap confidence intervals

Needs scikit-learn and matplotlib, so run it with the project's virtual environment:

    turnover_model/.venv/bin/python turnover_model/compare_models.py

Outputs (turnover_model/results/):
    model_comparison.csv, model_comparison.md   one row per model
    roc_curves.png        ROC curve per model
    gains_chart.png       share of leavers caught vs share of staff contacted
    calibration.png       predicted vs actual resignation rate
    lambda_tuning.png     how the penalty strength was chosen
    feature_importance.png  logistic regression weights vs random forest permutation importance
    confusion_matrix.png  what flagging the top 10% means in counts
"""
import argparse
import itertools
from pathlib import Path

import matplotlib

matplotlib.use("Agg")
import matplotlib.pyplot as plt
import numpy as np
import pandas as pd
from sklearn.ensemble import HistGradientBoostingClassifier, RandomForestClassifier
from sklearn.inspection import permutation_importance
from sklearn.metrics import average_precision_score, roc_curve
from sklearn.tree import DecisionTreeClassifier

from train_turnover_model import (
    CATEGORICAL, DATE, EXIT_TYPE, LABEL, MAX_MISSING_SHARE, NUMERIC, Preprocessor,
    auc, class_weight, fit_logistic, lambda_validation_scores, log_loss, precision_at,
    predict, purged_split, rolling_folds,
)

TOP_SHARE = 0.10          # HR's "High risk" list
BOOTSTRAP_ROUNDS = 1000
SEED = 0

# Plain names for charts
LABELS = {
    "grade": "Grade", "tenure_months": "Time with organisation", "months_since_promotion": "Months since promotion",
    "compa_ratio": "Pay vs band midpoint", "manager_changed_6m": "Recent manager change",
    "team_exits_6m": "Colleagues leaving", "avg_rating": "Average rating", "rating_change": "Rating trend",
    "self_vs_manager_rating_gap": "Rates self above assessors", "tasks_vs_dept_avg": "Tasks vs dept average",
    "late_task_pct": "Tasks logged late", "projects_assigned": "Projects assigned", "sick_days_q": "Sick days",
    "training_hours_12m": "Training hours", "days_since_last_login": "Days since sign-in",
}
COLOURS = {
    "Logistic regression": "#6c63ff", "Decision tree": "#f59e0b", "Random forest": "#10b981",
    "Gradient boosting": "#ef4444", "Rule: no promotion in 3 years": "#9ca3af",
}


def label_for(column):
    if column.endswith("__missing"):
        return LABELS.get(column[:-9], column[:-9]) + " (missing)"
    if "=" in column:
        field, level = column.split("=", 1)
        return f"{field.replace('_', ' ').capitalize()}: {level}"
    return LABELS.get(column, column)


# ----- Models -----------------------------------------------------------------------------
TREE_MODELS = {
    "Decision tree": (
        lambda max_depth, min_samples_leaf: DecisionTreeClassifier(
            max_depth=max_depth, min_samples_leaf=min_samples_leaf, random_state=SEED),
        {"max_depth": [2, 3, 4, 5], "min_samples_leaf": [20, 50, 100]},
    ),
    "Random forest": (
        lambda max_depth, min_samples_leaf: RandomForestClassifier(
            n_estimators=300, max_depth=max_depth, min_samples_leaf=min_samples_leaf,
            max_features="sqrt", n_jobs=-1, random_state=SEED),
        {"max_depth": [4, 6, None], "min_samples_leaf": [10, 30]},
    ),
    "Gradient boosting": (
        lambda learning_rate, max_depth, max_iter: HistGradientBoostingClassifier(
            learning_rate=learning_rate, max_depth=max_depth, max_iter=max_iter,
            l2_regularization=1.0, random_state=SEED),
        {"learning_rate": [0.03, 0.1], "max_depth": [2, 3], "max_iter": [100, 300]},
    ),
}


def grid(params):
    keys = list(params)
    for values in itertools.product(*(params[k] for k in keys)):
        yield dict(zip(keys, values))


def tune_tree_model(make, params, train_df, numeric, horizon):
    """Pick the setting with the lowest mean validation log-loss on rolling folds."""
    y = train_df[LABEL].to_numpy()
    folds = []
    for fold_train, fold_val in rolling_folds(train_df, horizon):
        prep = Preprocessor().fit(train_df[fold_train], numeric, CATEGORICAL)
        folds.append((prep.transform(train_df[fold_train]), y[fold_train],
                      prep.transform(train_df[fold_val]), y[fold_val]))
    best, best_loss = None, np.inf
    for setting in grid(params):
        losses = [log_loss(make(**setting).fit(X_tr, y_tr).predict_proba(X_val)[:, 1], y_val)
                  for X_tr, y_tr, X_val, y_val in folds]
        if np.mean(losses) < best_loss:
            best, best_loss = setting, np.mean(losses)
    return best, best_loss


# ----- Metrics ----------------------------------------------------------------------------
def metrics(score, y, probabilistic):
    k = max(1, int(round(TOP_SHARE * len(score))))
    top = np.argsort(-score, kind="stable")[:k]
    base = y.mean()
    precision = y[top].mean()
    row = {
        "auc": auc(score, y),
        "avg_precision": average_precision_score(y, score),
        "precision_top": precision,
        "recall_top": y[top].sum() / y.sum(),
        "lift_top": precision / base,
        "log_loss": log_loss(score, y) if probabilistic else np.nan,
        "brier": float(np.mean((score - y) ** 2)) if probabilistic else np.nan,
    }
    return row


def bootstrap_indices(n, rounds, seed):
    rng = np.random.default_rng(seed)
    return [rng.integers(0, n, n) for _ in range(rounds)]


def auc_interval(score, y, samples):
    values = [auc(score[i], y[i]) for i in samples]
    return np.nanpercentile(values, [2.5, 97.5])


def auc_difference_interval(score, reference, y, samples):
    """95% interval for AUC(model) - AUC(reference) on the same resampled test rows."""
    values = [auc(score[i], y[i]) - auc(reference[i], y[i]) for i in samples]
    return np.nanpercentile(values, [2.5, 97.5])


# ----- Figures ----------------------------------------------------------------------------
def style(ax, title, xlabel, ylabel):
    ax.set_title(title, fontsize=12, fontweight="bold", loc="left")
    ax.set_xlabel(xlabel)
    ax.set_ylabel(ylabel)
    ax.grid(alpha=0.25)
    for side in ("top", "right"):
        ax.spines[side].set_visible(False)


def plot_roc(scores, y, results, path, title="ROC curves on the held-out test period"):
    fig, ax = plt.subplots(figsize=(6.4, 5.2))
    for name, score in scores.items():
        fpr, tpr, _ = roc_curve(y, score)
        ax.plot(fpr, tpr, lw=2 if name == "Logistic regression" else 1.5, color=COLOURS[name],
                label=f"{name} (AUC {results[name]['auc']:.3f})")
    ax.plot([0, 1], [0, 1], "--", color="#6b7280", lw=1, label="Random (AUC 0.500)")
    style(ax, title, "False positive rate", "True positive rate")
    ax.legend(loc="lower right", fontsize=8.5, frameon=False)
    fig.tight_layout()
    fig.savefig(path, dpi=200)
    plt.close(fig)


def plot_gains(scores, y, path, title="Cumulative gains on the test period"):
    fig, ax = plt.subplots(figsize=(6.4, 5.2))
    share = np.arange(1, len(y) + 1) / len(y)
    for name, score in scores.items():
        caught = np.cumsum(y[np.argsort(-score, kind="stable")]) / y.sum()
        ax.plot(share * 100, caught * 100, lw=2 if name == "Logistic regression" else 1.5,
                color=COLOURS[name], label=name)
    ax.plot([0, 100], [0, 100], "--", color="#6b7280", lw=1, label="Random")
    ax.axvline(TOP_SHARE * 100, color="#111827", lw=0.8, ls=":")
    ax.text(TOP_SHARE * 100 + 1, 4, f"Top {TOP_SHARE:.0%}\n(High band)", fontsize=8)
    style(ax, title,
          "Staff contacted, highest risk first (%)", "Leavers caught (%)")
    ax.legend(loc="lower right", fontsize=8.5, frameon=False)
    fig.tight_layout()
    fig.savefig(path, dpi=200)
    plt.close(fig)


def plot_calibration(probabilities, y, path, bins=5, title="Calibration: does 'x% risk' mean x% resign?"):
    fig, ax = plt.subplots(figsize=(6.4, 5.2))
    top = 0
    for name, p in probabilities.items():
        groups = pd.qcut(p, bins, labels=False, duplicates="drop")
        predicted = [p[groups == g].mean() for g in sorted(set(groups))]
        actual = [y[groups == g].mean() for g in sorted(set(groups))]
        top = max(top, max(predicted), max(actual))
        ax.plot(np.array(predicted) * 100, np.array(actual) * 100, "o-", color=COLOURS[name],
                lw=2 if name == "Logistic regression" else 1.5, ms=5, label=name)
    limit = top * 100 * 1.1
    ax.plot([0, limit], [0, limit], "--", color="#6b7280", lw=1, label="Perfect calibration")
    ax.set_xlim(0, limit)
    ax.set_ylim(0, limit)
    style(ax, title,
          f"Predicted resignation rate (%, {bins} equal-size groups)", "Actual resignation rate (%)")
    ax.legend(loc="upper left", fontsize=8.5, frameon=False)
    fig.tight_layout()
    fig.savefig(path, dpi=200)
    plt.close(fig)


def plot_lambda(lambda_scores, chosen, path):
    lams = sorted(lambda_scores)
    losses = [lambda_scores[l][0] for l in lams]
    aucs = [lambda_scores[l][1] for l in lams]
    fig, ax = plt.subplots(figsize=(6.4, 4.8))
    ax.plot(lams, losses, "o-", color="#6c63ff", lw=2, label="Validation log-loss (lower is better)")
    ax.set_xscale("log")
    ax.axvline(chosen, color="#111827", lw=0.8, ls=":")
    ax.annotate(f"chosen λ = {chosen}", xy=(chosen, min(losses)), xytext=(min(lams) * 1.3, min(losses) + 0.3 * (max(losses) - min(losses))),
                fontsize=8.5, arrowprops={"arrowstyle": "->", "lw": 0.8})
    style(ax, "Choosing the penalty strength λ", "λ (log scale)", "Mean validation log-loss")
    twin = ax.twinx()
    twin.plot(lams, aucs, "s--", color="#10b981", lw=1.5, ms=4, label="Validation AUC")
    twin.set_ylabel("Mean validation AUC")
    twin.spines["top"].set_visible(False)
    lines = ax.get_lines()[:1] + twin.get_lines()
    ax.legend(lines, [l.get_label() for l in lines], loc="upper center", bbox_to_anchor=(0.5, -0.17),
              ncol=2, fontsize=8.5, frameon=False)
    fig.tight_layout()
    fig.savefig(path, dpi=200)
    plt.close(fig)


def plot_importance(columns, weights, permutation, path, top=12, label=None, right_xlabel="Drop in test AUC when the signal is shuffled"):
    label = label or label_for
    fig, (left, right) = plt.subplots(1, 2, figsize=(12, 5.4))
    order = np.argsort(-np.abs(weights))[:top][::-1]
    left.barh([label(columns[i]) for i in order], weights[order],
              color=["#ef4444" if weights[i] > 0 else "#10b981" for i in order])
    left.axvline(0, color="#111827", lw=0.8)
    style(left, "Logistic regression: standardised weights", "Weight (red raises risk, green lowers it)", "")
    order = np.argsort(-permutation)[:top][::-1]
    right.barh([label(columns[i]) for i in order], permutation[order], color="#10b981")
    style(right, "Random forest: permutation importance", right_xlabel, "")
    for ax in (left, right):
        ax.tick_params(axis="y", labelsize=8.5)
    fig.tight_layout()
    fig.savefig(path, dpi=200)
    plt.close(fig)


def plot_confusion(score, y, path):
    k = max(1, int(round(TOP_SHARE * len(score))))
    flagged = np.zeros(len(score), dtype=bool)
    flagged[np.argsort(-score, kind="stable")[:k]] = True
    matrix = np.array([[np.sum(flagged & (y == 1)), np.sum(~flagged & (y == 1))],
                       [np.sum(flagged & (y == 0)), np.sum(~flagged & (y == 0))]])
    fig, ax = plt.subplots(figsize=(5.6, 4.6))
    ax.imshow(matrix, cmap="Purples")
    for (r, c), value in np.ndenumerate(matrix):
        ax.text(c, r, f"{value}\n({value / len(y):.1%})", ha="center", va="center", fontsize=11,
                color="white" if value > matrix.max() / 2 else "#111827")
    ax.set_xticks([0, 1], [f"Flagged High\n(top {TOP_SHARE:.0%})", "Not flagged"])
    ax.set_yticks([0, 1], ["Resigned\nwithin 6 months", "Stayed"])
    ax.set_title(f"Flagging the top {TOP_SHARE:.0%} (logistic regression)", fontsize=11, fontweight="bold", loc="left")
    fig.tight_layout()
    fig.savefig(path, dpi=200)
    plt.close(fig)


# ----- Main -------------------------------------------------------------------------------
def main():
    here = Path(__file__).resolve().parent
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--train", default=here / "turnover_train.csv")
    parser.add_argument("--out-dir", default=here / "results")
    parser.add_argument("--horizon-days", type=int, default=182)
    parser.add_argument("--test-quarters", type=int, default=3)
    args = parser.parse_args()
    out = Path(args.out_dir)
    out.mkdir(exist_ok=True)
    horizon = pd.Timedelta(days=args.horizon_days)

    # Same preparation as train_turnover_model.py
    data = pd.read_csv(args.train)
    data[EXIT_TYPE] = data[EXIT_TYPE].fillna("")
    data = data[~((data[EXIT_TYPE] != "") & (data[LABEL] == 0))].reset_index(drop=True)
    dates = pd.to_datetime(data[DATE])
    first_test = sorted(dates.unique())[-args.test_quarters]
    train_mask, test_mask = purged_split(dates, first_test, horizon)
    train_df, test_df = data[train_mask.to_numpy()], data[test_mask.to_numpy()]
    missing = train_df[NUMERIC].isna().mean()
    numeric = [c for c in NUMERIC if missing[c] <= MAX_MISSING_SHARE]
    y_train, y_test = train_df[LABEL].to_numpy(), test_df[LABEL].to_numpy()
    print(f"Train {train_df[DATE].min()}..{train_df[DATE].max()}: {len(train_df)} rows, {y_train.sum()} leavers")
    print(f"Test  {test_df[DATE].min()}..{test_df[DATE].max()}: {len(test_df)} rows, {y_test.sum()} leavers")

    prep = Preprocessor().fit(train_df, numeric, CATEGORICAL)
    X_train, X_test = prep.transform(train_df), prep.transform(test_df)
    probabilities, settings = {}, {}

    # Logistic regression, exactly as in production
    lambda_scores = lambda_validation_scores(train_df, numeric, horizon, "balanced")
    lam = min(lambda_scores, key=lambda l: lambda_scores[l][0])
    weights = fit_logistic(X_train, y_train, lam, class_weight(y_train, "balanced"))
    probabilities["Logistic regression"] = predict(weights, X_test)
    settings["Logistic regression"] = f"λ = {lam}, balanced class weight + prior correction"
    print(f"Logistic regression: λ = {lam}")

    fitted = {}
    for name, (make, params) in TREE_MODELS.items():
        best, loss = tune_tree_model(make, params, train_df, numeric, horizon)
        model = make(**best).fit(X_train, y_train)
        fitted[name] = model
        probabilities[name] = model.predict_proba(X_test)[:, 1]
        settings[name] = ", ".join(f"{k} = {v}" for k, v in best.items())
        print(f"{name}: {settings[name]} (validation log-loss {loss:.4f})")

    # Baselines: a simple HR rule, and the single best signal chosen on TRAINING data
    rule = (test_df["months_since_promotion"] >= 36).astype(float).to_numpy()
    single_auc = {c: auc(train_df[c].fillna(train_df[c].median()).to_numpy(), y_train) for c in numeric}
    best_signal = max(single_auc, key=lambda c: abs(single_auc[c] - 0.5))
    direction = 1 if single_auc[best_signal] >= 0.5 else -1
    single = direction * test_df[best_signal].fillna(train_df[best_signal].median()).to_numpy()
    base_probability = np.full(len(y_test), y_train.mean())

    # Metrics with bootstrap intervals (same resampled rows for every model)
    samples = bootstrap_indices(len(y_test), BOOTSTRAP_ROUNDS, SEED)
    reference = probabilities["Logistic regression"]
    rows = []
    candidates = list(probabilities.items()) + [
        ("Rule: no promotion in 3 years", rule),
        (f"Best single signal ({LABELS.get(best_signal, best_signal)})", single),
        ("No model (always predict the base rate)", base_probability),
    ]
    for name, score in candidates:
        probabilistic = name in probabilities or name.startswith("No model")
        row = {"model": name, **metrics(score, y_test, probabilistic)}
        if name.startswith("No model"):
            row.update(auc=0.5, avg_precision=y_test.mean(), precision_top=y_test.mean(),
                       recall_top=TOP_SHARE, lift_top=1.0)
            row["auc_low"] = row["auc_high"] = row["diff_low"] = row["diff_high"] = np.nan
        else:
            row["auc_low"], row["auc_high"] = auc_interval(score, y_test, samples)
            if name == "Logistic regression":
                row["diff_low"] = row["diff_high"] = np.nan
            else:
                row["diff_low"], row["diff_high"] = auc_difference_interval(score, reference, y_test, samples)
        row["settings"] = settings.get(name, "")
        rows.append(row)
    table = pd.DataFrame(rows)
    table.to_csv(out / "model_comparison.csv", index=False)

    # Markdown table for the report
    def fmt(v, pct=False):
        if pd.isna(v):
            return "–"
        return f"{v:.1%}" if pct else f"{v:.3f}"

    lines = [
        "| Model | AUC (95% CI) | AUC vs logistic regression (95% CI) | Avg precision | "
        f"Precision top {TOP_SHARE:.0%} | Recall top {TOP_SHARE:.0%} | Lift | Log-loss | Brier |",
        "|---|---|---|---|---|---|---|---|---|",
    ]
    for r in rows:
        ci = f"{r['auc']:.3f} ({r['auc_low']:.3f}–{r['auc_high']:.3f})" if not pd.isna(r["auc_low"]) else f"{r['auc']:.3f}"
        diff = "reference" if r["model"] == "Logistic regression" else (
            "–" if pd.isna(r["diff_low"]) else f"{r['auc'] - reference_auc(rows):+.3f} ({r['diff_low']:+.3f} to {r['diff_high']:+.3f})")
        lines.append(f"| {r['model']} | {ci} | {diff} | {fmt(r['avg_precision'])} | {fmt(r['precision_top'], True)} | "
                     f"{fmt(r['recall_top'], True)} | {r['lift_top']:.2f}× | {fmt(r['log_loss'])} | {fmt(r['brier'])} |")
    notes = [
        "",
        f"Test period {test_df[DATE].min()} to {test_df[DATE].max()}: {len(test_df)} employee-quarters, "
        f"{y_test.sum()} resigned within 6 months (base rate {y_test.mean():.1%}).",
        f"Training period {train_df[DATE].min()} to {train_df[DATE].max()}: {len(train_df)} rows, {y_train.sum()} leavers; "
        "6-month gap before the test period.",
        f"Confidence intervals: {BOOTSTRAP_ROUNDS} bootstrap resamples of the test rows. "
        "A difference interval that includes 0 means the models are not distinguishable on this data.",
        "Tuned settings: " + "; ".join(f"{k}: {v}" for k, v in settings.items()) + ".",
    ]
    (out / "model_comparison.md").write_text("\n".join(lines + notes) + "\n")
    print("\n" + "\n".join(lines + notes))

    # Figures
    curves = {k: probabilities[k] for k in probabilities}
    curves["Rule: no promotion in 3 years"] = rule
    plot_roc(curves, y_test, {r["model"]: r for r in rows}, out / "roc_curves.png")
    plot_gains(curves, y_test, out / "gains_chart.png")
    plot_calibration(probabilities, y_test, out / "calibration.png")
    plot_lambda(lambda_scores, lam, out / "lambda_tuning.png")
    importance = permutation_importance(fitted["Random forest"], X_test, y_test, scoring="roc_auc",
                                        n_repeats=10, random_state=SEED, n_jobs=-1).importances_mean
    plot_importance(prep.columns, weights[1:], importance, out / "feature_importance.png")
    plot_confusion(reference, y_test, out / "confusion_matrix.png")
    print(f"\nWrote model_comparison.csv/.md and 6 figures to {out}")


def reference_auc(rows):
    return next(r["auc"] for r in rows if r["model"] == "Logistic regression")


if __name__ == "__main__":
    main()
