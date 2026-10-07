# Employee turnover risk model

Estimates each employee's chance of **resigning within the next 6 months**, with the main reasons,
so managers can start supportive conversations early. The model is trained offline in Python; the
admin page `admin/risk.php` only displays the results.

> These scores are estimates for starting conversations. They are not a judgement of anyone and must
> never be used on their own for decisions about pay, promotion or dismissal.

**Status:** everything here currently runs on **synthetic data**. No real employee data has been used.
The admin page shows a "Demo data" banner until it is fed real, exported data.

---

## Contents

| File | What it is |
|---|---|
| `generate_turnover_data.py` | Makes realistic synthetic data (anonymous IDs only) |
| `check_data.py` | Sanity checks on the synthetic data: leakage, signal strength, learnability |
| `train_turnover_model.py` | Trains, evaluates and exports the model, and scores current staff |
| `compare_models.py` | Compares logistic regression with a decision tree, random forest, gradient boosting and simple baselines; writes the results table and report figures to `results/` |
| `results/` | `model_comparison.md`/`.csv` and six figures (ROC, gains, calibration, λ tuning, feature importance, confusion matrix) |
| `evaluate_ibm.py` | Runs the same models on the public IBM HR Attrition benchmark with nested cross-validation |
| `results/ibm/` | `ibm_model_comparison.md`/`.csv` and four figures for the benchmark |
| `data_dictionary.csv` | What every column means |
| `turnover_model.json` | The trained model: everything needed to score (committed) |
| `training_report.txt` | The evaluation from the last training run (committed) |
| `turnover_train.csv`, `turnover_score_current.csv`, `employees.csv` | Generated data (git-ignored) |
| `risk_scores.csv` | Current staff, highest risk first, with reasons (git-ignored; read by `admin/risk.php`) |
| `.htaccess` | Blocks web access to this folder |

All employee-level CSVs are git-ignored, because the same filenames will hold real data later.

---

## Quick start

Needs Python 3 with **numpy** and **pandas** only (no scikit-learn).

```bash
python3 turnover_model/generate_turnover_data.py      # synthetic data (optional: --employees 1000 --seed 7)
python3 turnover_model/check_data.py                  # optional sanity checks
python3 turnover_model/train_turnover_model.py        # train, evaluate, export, score
```

Then open **Admin → Extras → Turnover Risk**.

The model comparison also needs scikit-learn and matplotlib, kept in a local virtual environment
(git-ignored):

```bash
python3 -m venv turnover_model/.venv
turnover_model/.venv/bin/pip install numpy pandas scikit-learn matplotlib
turnover_model/.venv/bin/python turnover_model/compare_models.py     # about a minute
```

For the public benchmark, download `WA_Fn-UseC_-HR-Employee-Attrition.csv` from
[IBM HR Analytics Employee Attrition & Performance](https://www.kaggle.com/datasets/pavansubhasht/ibm-hr-analytics-attrition-dataset)
into `turnover_model/` (git-ignored), then:

```bash
turnover_model/.venv/bin/python turnover_model/evaluate_ibm.py        # about 8 minutes
```

To train on real data, export the same columns (see [Data](#data)) and run:

```bash
python3 turnover_model/train_turnover_model.py --train real_train.csv --score real_current.csv
```

| Option | Default | Meaning |
|---|---|---|
| `--horizon-days` | 182 | "Resigned within" window for the label |
| `--test-quarters` | 3 | Latest snapshots held out for the final test |
| `--class-weight` | `balanced` | `balanced` or `none` (see [Training](#training)) |
| `--high-risk-share` | 0.10 | Share of staff labelled **High** |
| `--medium-risk-share` | 0.20 | Next share labelled **Medium** |
| `--out-dir` | this folder | Where outputs are written |

---

## How it fits together

```
                 ┌──────────────────────────┐
 HR / app data ─▶│ export (one row per      │   (not built yet: today the synthetic
                 │ employee per quarter)    │    generator stands in for it)
                 └────────────┬─────────────┘
                              ▼
                 train_turnover_model.py  ──▶  turnover_model.json   (weights, means, stds, medians)
                              │          ──▶  training_report.txt
                              ▼
                       risk_scores.csv  ──▶  admin/risk.php  (ranked list, reasons, drift warning)
```

Scoring needs only the weights in `turnover_model.json` and one formula, so it could also run
directly in PHP with no Python on the server (see [Scoring in PHP](#scoring-in-php)).

---

## Data

**One row = one employee at one quarter-end snapshot.** Every feature uses only information known on
that date. Full descriptions are in `data_dictionary.csv`.

| Group | Columns |
|---|---|
| Keys | `snapshot_date`, `employee_id` |
| Who | `department`, `role`, `contract_type`, `grade`, `tenure_months` |
| Career and pay | `months_since_promotion`, `compa_ratio` (pay ÷ band midpoint) |
| Team | `manager_changed_6m`, `team_exits_6m` |
| Performance (from the app) | `avg_rating`, `rating_change`, `self_vs_manager_rating_gap` |
| Engagement (from the app) | `tasks_logged`, `tasks_vs_dept_avg`, `late_task_pct`, `projects_assigned`, `days_since_last_login` |
| Wellbeing / investment | `sick_days_q`, `training_hours_12m` |
| **Label** | `resigned_within_6m`: 1 if they resigned within 182 days after the snapshot |
| **Outcome** (not a feature) | `exit_type_within_6m`: any exit type in the window |

**Rules that keep the evaluation honest:**

- The label and `exit_type_within_6m` are answers, **never inputs**.
- Rows where someone was dismissed, retired or reached the end of their contract in the window are
  dropped. The model learns **voluntary** leaving only.
- A row's label is only used once its full 6-month window has passed. The most recent snapshots go
  into the scoring file instead.
- No names, contact details or sensitive attributes (gender, age, ethnicity, religion, health).
  Free-text comments are not used as features.

### The synthetic data

`generate_turnover_data.py` simulates about 450 employees in 8 departments over 12 quarters
(Dec 2023 to Sep 2026), with the app's roles and 10–100 rating scale.

- A hidden "intention to leave" builds up from realistic causes: stalled promotion, pay below band,
  a manager change, colleagues leaving, early tenure, and department culture.
- That intention shows up in the observable signals **before** people resign: ratings dip, fewer
  tasks, more late tasks, more sick days, longer gaps between logins.
- Exits are resigned / dismissed / retired / contract ended, and leavers are backfilled by new hires.
- `days_since_last_login` is blank before Jul 2025 and `rating_change` on first snapshots, to
  mimic real missing data.

Result: about 14–18% annual resignations with a stable headcount. The signal is deliberately modest,
because a dataset a model could predict perfectly would set false expectations.

---

## Training

### 1. Prepare inputs

| Step | How | Why |
|---|---|---|
| Drop sparse signals | Any numeric column blank in more than 40% of **training** rows is skipped | Can't learn from a column that didn't exist yet (e.g. `days_since_last_login` today) |
| Missing values | Fill with the training median and add a `<column>__missing` 0/1 flag | "Unknown" can itself be informative; dropping rows wastes scarce leavers |
| Categories | One 0/1 column per level, e.g. `department=IT` | The formula needs numbers |
| Scale | `z = (x − mean) / std`, using training rows only | Comparable weights, and the penalty treats every signal fairly |

### 2. Split by time, with a gap

```
Train   Dec 2023 ── Mar 2025         (learn weights)
Gap                   Apr–Jun 2025   (dropped)
Test                            Sep 2025 ── Mar 2026   (grade it; never seen in training)
```

The gap matters. A training row's label looks 6 months ahead, so training rows must end at least
6 months before the test period, or their answers would leak into it. Splitting at random would
leak the future and inflate every score.

### 3. The model: ridge-regularised logistic regression

Score and probability:

```
z = b0 + b1·x1 + b2·x2 + … + bk·xk          (x = standardised signals)
p = 1 / (1 + e^(−z))                         (chance of resigning within 6 months)
```

Weights minimise class-weighted log-loss plus a ridge penalty (the intercept is not penalised):

```
Loss = −(1/S) Σ sᵢ [ yᵢ·log(pᵢ) + (1 − yᵢ)·log(1 − pᵢ) ]  +  λ Σ bⱼ²

sᵢ = w for leavers, 1 for stayers,   w = stayers / leavers   ("balanced")
S  = Σ sᵢ
```

- **Class weight `w`:** only about 7% of rows are resignations. Without it, "nobody ever leaves" looks
  93% accurate.
- **Ridge penalty `λ`:** keeps weights small unless the data supports them. Essential with only a
  few hundred leavers.

Fitted by **Newton's method**, which converges exactly in about 10 steps:

```
gradient = (1/S) Xᵀ(s ⊙ (p − y)) + 2λb
Hessian  = (1/S) Xᵀ diag(s ⊙ p ⊙ (1 − p)) X + 2λI
b ← b − Hessian⁻¹ · gradient
```

**Prior correction.** Weighting leavers by `w` adds `log(w)` to every fitted log-odds, which inflates
probabilities. The script subtracts it from the intercept afterwards (`b0 ← b0 − log w`), so the
output is a real probability again.

### 4. Choosing λ

Rolling validation **inside the training period only**. For each of the last 3 training quarters, the
model is trained on rows at least 6 months earlier and validated on that quarter. The script tries
λ ∈ {0.001, 0.003, 0.01, 0.03, 0.1, 0.3, 1, 3} and picks the one with the **lowest mean validation
log-loss**. Log-loss rewards both good ranking and honest probabilities; choosing by AUC alone tends
to shrink every weight towards zero.

### 5. Final model

After evaluation, the model is refitted on **all** labelled rows with the chosen λ and exported.

---

## Evaluation

| Metric | Meaning |
|---|---|
| **AUC** | Chance a random leaver is ranked above a random stayer. 0.5 = guessing |
| **Precision in top 10% / lift** | Of the 10% flagged, how many resigned, compared with the overall rate. What HR actually experiences |
| **Log-loss** | Quality of the probabilities; compared with always predicting the base rate |
| **Brier score** | `mean((p − y)²)`; lower is better; useful for comparing retrains |
| **Calibration** | Test rows in 5 bins: does "8% risk" mean about 8% resign? |
| **Baselines** | Random (0.5), the rule "no promotion in 3 years", and the best single signal. The model must beat simple rules to be worth using |

### Results on synthetic data (last run)

Trained on 2,554 rows (169 leavers), tested on 1,284 rows (114 leavers), λ = 0.1.

| | |
|---|---|
| AUC | **0.627** |
| Top 10% flagged who resigned | **18.0%** vs 8.9% overall → **2.0× lift** |
| Log-loss | 0.2930 (base-rate guess: 0.3035) |
| Brier score | 0.0792 |
| Rule "no promotion in 3 yrs" | AUC 0.513 |
| Best single signal (`late_task_pct`) | AUC 0.626 |

Calibration (predicted vs actual): 3.3%→5.8%, 4.7%→5.4%, 6.0%→8.2%, 7.9%→9.3%, 13.3%→15.6%.
The ordering is right but it under-predicts slightly, because resignations rose in the test period
(see the drift check).

Strongest signals (odds ratio per 1 standard deviation):

| Raises risk | | Lowers risk | |
|---|---|---|---|
| Tasks logged late | ×1.19 | Tasks logged vs department average | ×0.92 |
| Rates self above assessors | ×1.15 | Average rating | ×0.93 |
| Sick days | ×1.13 | Role: Auditor | ×0.94 |
| Department: IT | ×1.09 | Department: Legal / Finance | ×0.94 |
| Recent manager change | ×1.09 | Pay vs pay-band midpoint | ×0.95 |

### Model comparison

Same split, same tuning procedure (lowest validation log-loss on rolling folds inside the training
period), 95% bootstrap intervals over 1,000 resamples of the test rows. Full table:
`results/model_comparison.md`.

| Model | Test AUC (95% CI) | AUC vs logistic regression | Top-10% lift |
|---|---|---|---|
| Logistic regression | 0.627 (0.572–0.682) | reference | 2.02× |
| Random forest | 0.660 (0.610–0.714) | +0.032 (−0.001 to +0.070) | 2.29× |
| Gradient boosting | 0.601 (0.548–0.658) | −0.026 (−0.058 to +0.009) | 2.20× |
| Decision tree | 0.534 (0.497–0.575) | −0.093 (−0.159 to −0.034) | 1.67× |
| Best single signal (late tasks) | 0.626 (0.571–0.683) | −0.001 (−0.042 to +0.041) | 2.29× |
| Rule: no promotion in 3 years | 0.513 (0.475–0.557) | −0.114 (−0.170 to −0.058) | 0.97× |

The random forest scores highest, but its gain over logistic regression is not statistically clear
(the interval includes 0). Logistic regression is kept because it matches the best models within the
margin of error while giving exact, per-person reasons. The forest also ranks pay vs band highly,
which logistic regression barely uses: pay matters only below a threshold, a non-linear effect that
trees capture and a linear model cannot.

### Public benchmark: IBM HR Attrition

The same models on IBM's widely used benchmark (1,470 fictional employees, 16.1% attrition). It has
no dates, so evaluation uses **nested stratified cross-validation**: 10 outer folds, each model tuned
on 5 inner folds of its own training part. It is run with all features (comparable with published
studies) and without Age, Gender and MaritalStatus (as in the KPI app). Full tables:
`results/ibm/ibm_model_comparison.md`.

| Model | AUC, all features (95% CI) | vs logistic regression | AUC without sensitive attributes | Top-10% lift |
|---|---|---|---|---|
| Logistic regression | **0.833** (0.803–0.863) | reference | 0.825 | 4.22× |
| Gradient boosting | 0.817 (0.782–0.847) | −0.017 (−0.038 to +0.003) | 0.810 | 4.18× |
| Random forest | 0.801 (0.767–0.833) | −0.033 (−0.053 to −0.011) | 0.795 | 3.76× |
| Decision tree | 0.708 (0.668–0.746) | −0.126 (−0.164 to −0.089) | 0.709 | 3.21× |
| Rule: works overtime | 0.651 | −0.183 | 0.651 | 1.81× |

- **The method holds up on independent data:** logistic regression reaches AUC 0.83, in the range
  typically reported for this dataset, and is significantly better than the random forest and the
  decision tree. Gradient boosting is not distinguishable from it.
- **Fairness costs almost nothing:** removing Age, Gender and MaritalStatus lowers AUC by only 0.009.
- **Why higher than on the synthetic data:** the IBM data contains direct satisfaction scores
  (environment, job, work-life balance) and overtime, which are strong signals the KPI app does not
  record yet. This supports adding pulse surveys as future work.
- **Read the weights with care:** tenure columns (years at company, in role, with current manager)
  are strongly correlated, so their individual signs are not reliable. The random forest's
  permutation importance, which treats each original feature as one unit, puts overtime far ahead.

**Read honestly:** the model makes HR's shortlist about twice as accurate as picking at random, but
it only just beats ranking by late tasks alone. Its main added value is combining signals and giving
reasons. Expect real data to land around AUC 0.65–0.80, depending on how much HR data (promotions,
pay, absence) is available.

---

## Outputs

### `risk_scores.csv`

`employee_id, department, role, risk_probability, risk_band, reason_1, reason_2, reason_3`

- **Bands are ranks, not fixed cut-offs:** High = riskiest 10% of the people scored, Medium = the next
  20%, Low = the rest. HR always gets a short, workable list.
- **Reasons:** each signal's contribution to a person's score is `weight × standardised value`. The
  top 3 positive contributions, each at least 15% of that person's largest, are written in plain
  words, e.g. *"76% of tasks logged late"*. Technical `__missing` flags are never shown.

### Drift check

The script compares the current average risk with the training history. If more than 1.5× the
expected share of staff exceed the historical top-10% threshold, it warns. The admin page shows a
warning when average risk is more than 1.25× its historical level. Rising risk across the board
usually means an organisation-wide cause (pay, restructuring, workload), not individual cases.

### `turnover_model.json`

| Key | Contents |
|---|---|
| `numeric_features`, `numeric_medians` | Signals used, and the fill value for blanks |
| `missing_flag_features` | Signals that get a `__missing` flag |
| `categorical_levels` | Known levels per category |
| `columns`, `means`, `stds` | Model columns and their standardisation |
| `intercept`, `weights` | The model |
| `test_metrics`, `lambda`, `training_rows`, `training_positives`, `trained_at` | Provenance |
| `risk_bands` | Band shares and historical reference probabilities |

It holds only averages and weights, with no individual records, so it is safe to commit.

### Scoring in PHP

Everything needed to score one employee is in the JSON. `$row` is one employee's values:

```php
$z = $model['intercept'];
foreach ($model['columns'] as $column) {
    if (str_ends_with($column, '__missing')) {
        $feature = substr($column, 0, -9);
        $x = ($row[$feature] ?? '') === '' ? 1.0 : 0.0;
    } elseif (str_contains($column, '=')) {
        [$feature, $level] = explode('=', $column, 2);
        $x = ($row[$feature] ?? null) == $level ? 1.0 : 0.0;
    } else {
        $x = ($row[$column] ?? '') === '' ? $model['numeric_medians'][$column] : (float)$row[$column];
    }
    $z += $model['weights'][$column] * ($x - $model['means'][$column]) / $model['stds'][$column];
}
$risk = 1 / (1 + exp(-$z));
```

This reproduces the Python scores exactly (verified on all 444 synthetic employees).

---

## Moving to real data

1. **Run the database migrations** (`migrations/001`–`004`). They record hire dates, status and exits,
   and replace employee deletion with "Mark as left".
2. **Backfill from HR:** hire dates for current staff, plus past leavers (exit date and type).
3. **Add a monthly snapshot table** (not built yet). Role, department and ratings are overwritten
   when they change, so history must be captured as it happens.
4. **Build the export** (not built yet). It produces the training and scoring CSVs in the format above,
   with `employee_id` = `employee.id`, so the admin page shows real names automatically.
5. **Add HR signals where possible:** promotion/grade history, pay band position and absence records
   are usually the strongest predictors.
6. **Wait for enough history:** roughly 50 or more recorded resignations before trusting a trained
   model. Until then, use the scores only as a rough guide.
7. **Retrain every quarter or half-year,** and compare each new `training_report.txt` with the last.

### Fairness and privacy checklist

- [ ] Data-protection approval obtained (e.g. Ghana Data Protection Act) before using real data
- [ ] Only admins and HR can open the risk page
- [ ] No sensitive attributes in the features
- [ ] Each retrain: compare average scores and errors across departments and grades
- [ ] Staff told that the system exists and what it is used for
- [ ] Real data never committed and never uploaded to third-party services

---

## Limitations

- **Trained on synthetic data:** the current weights describe a made-up organisation.
- **Modest signal:** turnover is noisy, so individual predictions are uncertain. Use the ranking, not
  the exact percentage.
- **Linear model:** combinations (e.g. "underpaid **and** a new manager") are not captured. Once there
  are 300+ resignations, try gradient boosting as a challenger, and switch only if it clearly beats
  this model on the time-split test.
- **Answers "within 6 months?", not "when?":** survival analysis (e.g. a Cox model) is a later option.
