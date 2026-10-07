# IBM HR Attrition benchmark

Dataset: IBM HR Analytics Employee Attrition & Performance (Kaggle). 1470 employees, 237 left (16.1%). Fictional data created by IBM; a standard public benchmark.
Method: nested stratified cross-validation (10 outer folds for evaluation, 5 inner folds for tuning by lowest log-loss). Pooled metrics use the out-of-fold prediction for every employee. Confidence intervals: 1000 bootstrap resamples.

## All features (comparable with published studies)

| Model | AUC, mean ± SD over 10 folds | Pooled AUC (95% CI) | AUC vs logistic regression (95% CI) | Avg precision | Precision top 10% | Lift | Log-loss | Brier |
|---|---|---|---|---|---|---|---|---|
| Logistic regression | 0.837 ± 0.035 | 0.833 (0.803–0.863) | reference | 0.617 | 68.0% | 4.22× | 0.327 | 0.096 |
| Decision tree | 0.709 ± 0.062 | 0.708 (0.668–0.746) | -0.126 (-0.164 to -0.089) | 0.390 | 51.7% | 3.21× | 0.395 | 0.119 |
| Random forest | 0.802 ± 0.067 | 0.801 (0.767–0.833) | -0.033 (-0.053 to -0.011) | 0.529 | 60.5% | 3.76× | 0.358 | 0.108 |
| Gradient boosting | 0.820 ± 0.054 | 0.817 (0.782–0.847) | -0.017 (-0.038 to +0.003) | 0.585 | 67.3% | 4.18× | 0.334 | 0.098 |
| Rule: works overtime | – | 0.651 (0.615–0.685) | -0.183 (-0.217 to -0.149) | 0.238 | 29.3% | 1.81× | – | – |
| No model (always predict the base rate) | – | 0.500 | – | 0.161 | 16.1% | 1.00× | 0.442 | 0.135 |

## Without sensitive attributes (Age, Gender, MaritalStatus), as in the KPI app

| Model | AUC, mean ± SD over 10 folds | Pooled AUC (95% CI) | AUC vs logistic regression (95% CI) | Avg precision | Precision top 10% | Lift | Log-loss | Brier |
|---|---|---|---|---|---|---|---|---|
| Logistic regression | 0.827 ± 0.036 | 0.825 (0.792–0.854) | reference | 0.603 | 70.1% | 4.35× | 0.333 | 0.098 |
| Decision tree | 0.709 ± 0.059 | 0.709 (0.668–0.748) | -0.116 (-0.154 to -0.078) | 0.378 | 47.6% | 2.95× | 0.397 | 0.120 |
| Random forest | 0.798 ± 0.063 | 0.795 (0.760–0.828) | -0.029 (-0.051 to -0.008) | 0.522 | 56.5% | 3.50× | 0.361 | 0.109 |
| Gradient boosting | 0.812 ± 0.055 | 0.810 (0.773–0.842) | -0.015 (-0.035 to +0.004) | 0.571 | 61.2% | 3.80× | 0.339 | 0.100 |
| Rule: works overtime | – | 0.651 (0.615–0.685) | -0.174 (-0.209 to -0.139) | 0.238 | 29.3% | 1.81× | – | – |
| No model (always predict the base rate) | – | 0.500 | – | 0.161 | 16.1% | 1.00× | 0.442 | 0.135 |

## Same method on both datasets (AUC)

| Model | Synthetic KPI data (time split) | IBM, all features | IBM, without sensitive attributes | Cost of dropping them |
|---|---|---|---|---|
| Logistic regression | 0.627 | 0.833 | 0.825 | -0.009 |
| Decision tree | 0.534 | 0.708 | 0.709 | +0.001 |
| Random forest | 0.660 | 0.801 | 0.795 | -0.005 |
| Gradient boosting | 0.601 | 0.817 | 0.810 | -0.007 |

Most common tuned settings (all features): Logistic regression: λ=0.01; Decision tree: max_depth=3, min_samples_leaf=50; Random forest: max_depth=None, min_samples_leaf=10; Gradient boosting: learning_rate=0.1, max_depth=2, max_iter=100.
