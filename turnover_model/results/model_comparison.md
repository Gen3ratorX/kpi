| Model | AUC (95% CI) | AUC vs logistic regression (95% CI) | Avg precision | Precision top 10% | Recall top 10% | Lift | Log-loss | Brier |
|---|---|---|---|---|---|---|---|---|
| Logistic regression | 0.627 (0.572–0.682) | reference | 0.175 | 18.0% | 20.2% | 2.02× | 0.293 | 0.079 |
| Decision tree | 0.534 (0.497–0.575) | -0.093 (-0.159 to -0.034) | 0.113 | 14.8% | 16.7% | 1.67× | 0.296 | 0.080 |
| Random forest | 0.660 (0.610–0.714) | +0.032 (-0.001 to +0.070) | 0.180 | 20.3% | 22.8% | 2.29× | 0.288 | 0.079 |
| Gradient boosting | 0.601 (0.548–0.658) | -0.026 (-0.058 to +0.009) | 0.140 | 19.5% | 21.9% | 2.20× | 0.296 | 0.080 |
| Rule: no promotion in 3 years | 0.513 (0.475–0.557) | -0.114 (-0.170 to -0.058) | 0.091 | 8.6% | 9.6% | 0.97× | – | – |
| Best single signal (Tasks logged late) | 0.626 (0.571–0.683) | -0.001 (-0.042 to +0.041) | 0.202 | 20.3% | 22.8% | 2.29× | – | – |
| No model (always predict the base rate) | 0.500 | – | 0.089 | 8.9% | 10.0% | 1.00× | 0.303 | 0.081 |

Test period 2025-09-30 to 2026-03-31: 1284 employee-quarters, 114 resigned within 6 months (base rate 8.9%).
Training period 2023-12-31 to 2025-03-31: 2554 rows, 169 leavers; 6-month gap before the test period.
Confidence intervals: 1000 bootstrap resamples of the test rows. A difference interval that includes 0 means the models are not distinguishable on this data.
Tuned settings: Logistic regression: λ = 0.1, balanced class weight + prior correction; Decision tree: max_depth = 2, min_samples_leaf = 20; Random forest: max_depth = None, min_samples_leaf = 10; Gradient boosting: learning_rate = 0.03, max_depth = 2, max_iter = 100.
