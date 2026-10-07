"""
Run the same models on a public benchmark: the IBM HR Analytics Employee Attrition & Performance
dataset (Kaggle, pavansubhasht; 1,470 fictional employees created by IBM data scientists).

Unlike the synthetic data it has no dates, so instead of a time split this uses **nested stratified
cross-validation**: 10 outer folds measure performance, and each model is tuned (lowest log-loss)
on 5 inner folds of its own training part only, so no test row ever influences tuning.

It runs twice:
  - all features, as in published studies (results comparable with the literature)
  - without sensitive attributes (Age, Gender, MaritalStatus), as the KPI app does
The gap between the two is the accuracy cost of the fairer design.

    turnover_model/.venv/bin/python turnover_model/evaluate_ibm.py

Input:   turnover_model/WA_Fn-UseC_-HR-Employee-Attrition.csv  (download from Kaggle; git-ignored)
Outputs: turnover_model/results/ibm/  (ibm_model_comparison.md/.csv and four figures)
"""
import argparse
import re
from pathlib import Path

import numpy as np
import pandas as pd
from sklearn.model_selection import StratifiedKFold

from compare_models import (
    COLOURS, TOP_SHARE, TREE_MODELS, auc_difference_interval, auc_interval, bootstrap_indices,
    grid, metrics, plot_calibration, plot_gains, plot_roc, style,
)
from train_turnover_model import LAMBDAS, Preprocessor, auc, class_weight, fit_logistic, log_loss, predict

TARGET = "Attrition"
DROP = ["EmployeeCount", "Over18", "StandardHours", "EmployeeNumber"]   # constant columns and the ID
SENSITIVE = ["Age", "Gender", "MaritalStatus"]
CATEGORICAL = ["BusinessTravel", "Department", "EducationField", "Gender", "JobRole", "MaritalStatus", "OverTime"]
OUTER_FOLDS, INNER_FOLDS, SEED = 10, 5, 0
BOOTSTRAP_ROUNDS = 1000
RULE_NAME = "Rule: works overtime"
COLOURS[RULE_NAME] = "#9ca3af"   # the chart functions read this shared colour table


def readable(column):
    """'YearsSinceLastPromotion' -> 'Years since last promotion', 'OverTime=Yes' -> 'Over time: Yes'."""
    def words(name):
        return re.sub(r"(?<=[a-z])(?=[A-Z])", " ", name).capitalize()
    if column.endswith("__missing"):
        return words(column[:-9]) + " (missing)"
    if "=" in column:
        field, level = column.split("=", 1)
        return f"{words(field)}: {level.replace('_', ' ')}"
    return words(column)


def load(path):
    data = pd.read_csv(path, encoding="utf-8-sig")   # the file starts with a byte-order mark
    y = (data[TARGET] == "Yes").astype(int).to_numpy()
    return data.drop(columns=DROP + [TARGET]), y


# ----- Tuning on inner folds only ---------------------------------------------------------
def inner_splits(y):
    return list(StratifiedKFold(INNER_FOLDS, shuffle=True, random_state=SEED).split(np.zeros(len(y)), y))


def tune_logistic(features, y, numeric, categorical):
    losses = {lam: [] for lam in LAMBDAS}
    for tr, va in inner_splits(y):
        prep = Preprocessor().fit(features.iloc[tr], numeric, categorical)
        X_tr, X_va = prep.transform(features.iloc[tr]), prep.transform(features.iloc[va])
        for lam in LAMBDAS:
            b = fit_logistic(X_tr, y[tr], lam, class_weight(y[tr], "balanced"))
            losses[lam].append(log_loss(predict(b, X_va), y[va]))
    return min(LAMBDAS, key=lambda lam: np.mean(losses[lam]))


def tune_tree(make, params, features, y, numeric, categorical):
    folds = []
    for tr, va in inner_splits(y):
        prep = Preprocessor().fit(features.iloc[tr], numeric, categorical)
        folds.append((prep.transform(features.iloc[tr]), y[tr], prep.transform(features.iloc[va]), y[va]))
    best, best_loss = None, np.inf
    for setting in grid(params):
        loss = np.mean([log_loss(make(**setting).fit(a, b).predict_proba(c)[:, 1], d) for a, b, c, d in folds])
        if loss < best_loss:
            best, best_loss = setting, loss
    return best


def permutation_drop(model_predict, prep, test_df, y_test, columns, rng, repeats=5):
    """Drop in AUC when one original column is shuffled (one-hot groups count as one feature)."""
    base = auc(model_predict(prep.transform(test_df)), y_test)
    drops = {}
    for column in columns:
        values = []
        for _ in range(repeats):
            shuffled = test_df.copy()
            shuffled[column] = rng.permutation(shuffled[column].to_numpy())
            values.append(base - auc(model_predict(prep.transform(shuffled)), y_test))
        drops[column] = np.mean(values)
    return drops


# ----- One full nested cross-validation run -----------------------------------------------
def run(features, y, label, importance=False):
    categorical = [c for c in CATEGORICAL if c in features.columns]
    numeric = [c for c in features.columns if c not in categorical]
    names = ["Logistic regression", *TREE_MODELS]
    oof = {name: np.zeros(len(y)) for name in names}
    fold_auc = {name: [] for name in names}
    chosen = {name: [] for name in names}
    rf_drops = []
    rng = np.random.default_rng(SEED)

    outer = StratifiedKFold(OUTER_FOLDS, shuffle=True, random_state=SEED)
    for fold, (tr, te) in enumerate(outer.split(np.zeros(len(y)), y), 1):
        train, test = features.iloc[tr], features.iloc[te]
        prep = Preprocessor().fit(train, numeric, categorical)
        X_tr, X_te = prep.transform(train), prep.transform(test)

        lam = tune_logistic(train, y[tr], numeric, categorical)
        b = fit_logistic(X_tr, y[tr], lam, class_weight(y[tr], "balanced"))
        oof["Logistic regression"][te] = predict(b, X_te)
        chosen["Logistic regression"].append(f"λ={lam}")

        for name, (make, params) in TREE_MODELS.items():
            setting = tune_tree(make, params, train, y[tr], numeric, categorical)
            model = make(**setting).fit(X_tr, y[tr])
            oof[name][te] = model.predict_proba(X_te)[:, 1]
            chosen[name].append(", ".join(f"{k}={v}" for k, v in setting.items()))
            if importance and name == "Random forest":
                rf_drops.append(permutation_drop(lambda X: model.predict_proba(X)[:, 1], prep, test, y[te],
                                                 list(features.columns), rng))
        for name in names:
            fold_auc[name].append(auc(oof[name][te], y[te]))
        print(f"  [{label}] fold {fold}/{OUTER_FOLDS} done")

    rule = (features["OverTime"] == "Yes").astype(float).to_numpy()
    return oof, fold_auc, chosen, rule, (pd.DataFrame(rf_drops).mean() if rf_drops else None)


def summarise(oof, fold_auc, rule, y):
    samples = bootstrap_indices(len(y), BOOTSTRAP_ROUNDS, SEED)
    reference = oof["Logistic regression"]
    rows = []
    for name, score in [*oof.items(), (RULE_NAME, rule), ("No model (always predict the base rate)", np.full(len(y), y.mean()))]:
        is_base = name.startswith("No model")
        row = {"model": name, **metrics(score, y, probabilistic=name in oof or is_base)}
        if is_base:
            row.update(auc=0.5, avg_precision=y.mean(), precision_top=y.mean(), recall_top=TOP_SHARE, lift_top=1.0)
        row["fold_auc_mean"] = np.mean(fold_auc[name]) if name in fold_auc else np.nan
        row["fold_auc_sd"] = np.std(fold_auc[name], ddof=1) if name in fold_auc else np.nan
        row["auc_low"], row["auc_high"] = (np.nan, np.nan) if is_base else auc_interval(score, y, samples)
        row["diff_low"], row["diff_high"] = ((np.nan, np.nan) if is_base or name == "Logistic regression"
                                             else auc_difference_interval(score, reference, y, samples))
        rows.append(row)
    return rows


def markdown_table(rows):
    reference = next(r["auc"] for r in rows if r["model"] == "Logistic regression")
    lines = [
        "| Model | AUC, mean ± SD over 10 folds | Pooled AUC (95% CI) | AUC vs logistic regression (95% CI) | "
        f"Avg precision | Precision top {TOP_SHARE:.0%} | Lift | Log-loss | Brier |",
        "|---|---|---|---|---|---|---|---|---|",
    ]
    for r in rows:
        folds = "–" if pd.isna(r["fold_auc_mean"]) else f"{r['fold_auc_mean']:.3f} ± {r['fold_auc_sd']:.3f}"
        pooled = f"{r['auc']:.3f}" if pd.isna(r["auc_low"]) else f"{r['auc']:.3f} ({r['auc_low']:.3f}–{r['auc_high']:.3f})"
        if r["model"] == "Logistic regression":
            diff = "reference"
        elif pd.isna(r["diff_low"]):
            diff = "–"
        else:
            diff = f"{r['auc'] - reference:+.3f} ({r['diff_low']:+.3f} to {r['diff_high']:+.3f})"
        ll = "–" if pd.isna(r["log_loss"]) else f"{r['log_loss']:.3f}"
        brier = "–" if pd.isna(r["brier"]) else f"{r['brier']:.3f}"
        lines.append(f"| {r['model']} | {folds} | {pooled} | {diff} | {r['avg_precision']:.3f} | "
                     f"{r['precision_top']:.1%} | {r['lift_top']:.2f}× | {ll} | {brier} |")
    return lines


def main():
    here = Path(__file__).resolve().parent
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--data", default=here / "WA_Fn-UseC_-HR-Employee-Attrition.csv")
    parser.add_argument("--out-dir", default=here / "results" / "ibm")
    args = parser.parse_args()
    out = Path(args.out_dir)
    out.mkdir(parents=True, exist_ok=True)

    features, y = load(args.data)
    print(f"{len(y)} employees, {y.sum()} left ({y.mean():.1%}); {features.shape[1]} features after dropping {', '.join(DROP)}")

    oof, fold_auc, chosen, rule, rf_drops = run(features, y, "all features", importance=True)
    all_rows = summarise(oof, fold_auc, rule, y)
    fair = features.drop(columns=SENSITIVE)
    oof_fair, fold_auc_fair, chosen_fair, rule_fair, _ = run(fair, y, "no sensitive attributes")
    fair_rows = summarise(oof_fair, fold_auc_fair, rule_fair, y)

    table = pd.concat([pd.DataFrame(all_rows).assign(features="all"),
                       pd.DataFrame(fair_rows).assign(features="without Age, Gender, MaritalStatus")])
    table.to_csv(out / "ibm_model_comparison.csv", index=False)

    # Same method on both datasets (synthetic results from compare_models.py, if present)
    synthetic_path = here / "results" / "model_comparison.csv"
    synthetic = pd.read_csv(synthetic_path).set_index("model") if synthetic_path.exists() else None
    by_name = {r["model"]: r for r in all_rows}
    fair_by_name = {r["model"]: r for r in fair_rows}
    both = ["| Model | Synthetic KPI data (time split) | IBM, all features | IBM, without sensitive attributes | Cost of dropping them |",
            "|---|---|---|---|---|"]
    for name in ["Logistic regression", *TREE_MODELS]:
        syn = f"{synthetic.loc[name, 'auc']:.3f}" if synthetic is not None and name in synthetic.index else "–"
        cost = fair_by_name[name]["auc"] - by_name[name]["auc"]
        both.append(f"| {name} | {syn} | {by_name[name]['auc']:.3f} | {fair_by_name[name]['auc']:.3f} | {cost:+.3f} |")

    def most_common(values):
        return pd.Series(values).value_counts().index[0]

    report = [
        "# IBM HR Attrition benchmark",
        "",
        f"Dataset: IBM HR Analytics Employee Attrition & Performance (Kaggle). {len(y)} employees, "
        f"{y.sum()} left ({y.mean():.1%}). Fictional data created by IBM; a standard public benchmark.",
        f"Method: nested stratified cross-validation ({OUTER_FOLDS} outer folds for evaluation, {INNER_FOLDS} inner folds "
        "for tuning by lowest log-loss). Pooled metrics use the out-of-fold prediction for every employee. "
        f"Confidence intervals: {BOOTSTRAP_ROUNDS} bootstrap resamples.",
        "",
        "## All features (comparable with published studies)",
        "",
        *markdown_table(all_rows),
        "",
        "## Without sensitive attributes (Age, Gender, MaritalStatus), as in the KPI app",
        "",
        *markdown_table(fair_rows),
        "",
        "## Same method on both datasets (AUC)",
        "",
        *both,
        "",
        "Most common tuned settings (all features): "
        + "; ".join(f"{name}: {most_common(v)}" for name, v in chosen.items()) + ".",
    ]
    (out / "ibm_model_comparison.md").write_text("\n".join(report) + "\n")
    print("\n" + "\n".join(report))

    # Figures (all-features run)
    curves = {**oof, RULE_NAME: rule}
    plot_roc(curves, y, {r["model"]: r for r in all_rows}, out / "roc_curves.png",
             title="IBM benchmark: ROC curves (10-fold cross-validation)")
    plot_gains(curves, y, out / "gains_chart.png", title="IBM benchmark: cumulative gains")
    plot_calibration(oof, y, out / "calibration.png", title="IBM benchmark: calibration")
    prep = Preprocessor().fit(features, [c for c in features.columns if c not in CATEGORICAL], CATEGORICAL)
    lam = tune_logistic(features, y, [c for c in features.columns if c not in CATEGORICAL], CATEGORICAL)
    weights = fit_logistic(prep.transform(features), y, lam, class_weight(y, "balanced"))[1:]
    plot_rf_importance(rf_drops, out / "feature_importance.png", prep.columns, weights)
    print(f"\nWrote ibm_model_comparison.md/.csv and 4 figures to {out}")


def plot_rf_importance(drops, path, columns, weights, top=12):
    """Left: logistic regression weights (all data). Right: random forest permutation importance per original feature."""
    import matplotlib.pyplot as plt
    fig, (left, right) = plt.subplots(1, 2, figsize=(12, 5.4))
    order = np.argsort(-np.abs(weights))[:top][::-1]
    left.barh([readable(columns[i]) for i in order], weights[order],
              color=["#ef4444" if weights[i] > 0 else "#10b981" for i in order])
    left.axvline(0, color="#111827", lw=0.8)
    style(left, "Logistic regression: standardised weights", "Weight (red raises risk, green lowers it)", "")
    ranked = drops.sort_values(ascending=False).head(top)[::-1]
    right.barh([readable(c) for c in ranked.index], ranked.to_numpy(), color="#10b981")
    style(right, "Random forest: permutation importance", "Drop in held-out AUC when the feature is shuffled", "")
    for ax in (left, right):
        ax.tick_params(axis="y", labelsize=8.5)
    fig.tight_layout()
    fig.savefig(path, dpi=200)
    plt.close(fig)


if __name__ == "__main__":
    main()
