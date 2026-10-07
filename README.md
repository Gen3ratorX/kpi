# KPI Management System with Employee Turnover Risk Prediction

A web application for managing staff Key Performance Indicators (KPIs): projects, tasks and
performance assessments. It is extended with a **machine-learning model that estimates which
employees are likely to resign within the next 6 months**, and why, so managers can start
supportive conversations early.

Final-year project. Built with PHP, MariaDB/MySQL, jQuery and Bootstrap; the model is built in Python.

---

## Contents

1. [Features](#1-features)
2. [Technology](#2-technology)
3. [Project structure](#3-project-structure)
4. [Setup](#4-setup)
5. [Demo accounts](#5-demo-accounts)
6. [How to test](#6-how-to-test)
7. [The machine-learning model](#7-the-machine-learning-model)
8. [Security](#8-security)
9. [Limitations and future work](#9-limitations-and-future-work)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Features

**Administrator portal** (`admin/`)
- Manage employees, roles, departments and units.
- Create projects, set a target and a deadline, and assign employees.
- Open a project for task entry, or switch it to assessment.
- **Mark an employee as left** (exit date, reason) instead of deleting them, so their tasks and ratings
  are kept. People who have left can be **reinstated**.
- **Turnover Risk page:** staff ranked by their chance of resigning, with the main reasons for each
  person, risk by department, the factors that drive risk, and a warning when risk rises across the
  whole organisation.

**Employee portal** (`employee/`), which adapts to each person's role:

| Role | Can do |
|---|---|
| Employee | Add, edit and delete their own tasks (with evidence files) while a project is open; rate themselves and view assessors' ratings during assessment |
| Manager | As an employee, plus rate the staff in their own department |
| Auditor | Rate anyone on the project at any time; close assessment |
| General Manager | Rate anyone on the project during assessment; close assessment |

**Turnover tracking:** hire date, last sign-in and every exit (date, type and reason) are recorded.
That is the data the model learns from.

---

## 2. Technology

| Part | Technology |
|---|---|
| Web app | PHP 8.2+ (no framework), jQuery, Bootstrap 5, Bootstrap Icons |
| Database | MariaDB 10.4+ (as in XAMPP); developed and tested on MariaDB 13 |
| Model training | Python 3 with numpy and pandas |
| Model comparison and charts | scikit-learn and matplotlib |
| Tests | bash and curl (end-to-end tests against the running app) |

---

## 3. Project structure

```
kpi/
├── index.php, auth.php        Sign-in page and sign-in handler
├── admin/                     Administrator pages; utils.php handles their AJAX requests
│   └── risk.php               Turnover Risk page
├── employee/                  Employee pages; utils.php handles their AJAX requests
├── controls/                  Data access and business logic, one class per entity
├── misc/                      Database connection, sign-in guards, shared helpers
├── static/                    CSS, JavaScript and images
├── uploads/tasks/             Evidence files people upload (never served as code; git-ignored)
├── kpi.sql                    Clean install: schema, roles and one administrator
├── kpi_data.sql               Demo install: schema plus demo users, projects and ratings
├── migrations/                Upgrades for databases created by older versions
├── tests/regression.sh        Automated end-to-end and security tests (86 checks)
└── turnover_model/            The machine-learning model (see turnover_model/README.md)
    ├── generate_turnover_data.py   Makes the synthetic training data
    ├── train_turnover_model.py     Trains, evaluates, exports and scores
    ├── compare_models.py           Compares four algorithms (results/)
    ├── evaluate_ibm.py             Validates on the public IBM benchmark (results/ibm/)
    └── turnover_model.json         The trained model, read by the Turnover Risk page
```

---

## 4. Setup

### 4.1 Requirements

- **PHP 8.2 or newer** with the `mysqli` and `fileinfo` extensions (both are on by default in XAMPP).
  8.2 is the minimum because the app uses `mysqli::execute_query` for its prepared statements.
- **MariaDB 10.4 or newer** (MySQL 8 has not been tested).
- **Python 3.10 or newer**, only for the machine-learning part.

### 4.2 Option A: XAMPP (Windows or macOS)

1. Install [XAMPP](https://www.apachefriends.org/) with PHP 8.2+, then start **Apache** and **MySQL**
   from the XAMPP control panel.
2. Copy the project folder into XAMPP's `htdocs` folder and name it `kpi`, so that you have
   `htdocs/kpi/index.php`.
3. Open **phpMyAdmin** (http://localhost/phpmyadmin), create a database called **`kpi`** with collation
   **`utf8mb4_general_ci`**, select it, and **import `kpi_data.sql`**.
4. Open **http://localhost/kpi/** and sign in with a [demo account](#5-demo-accounts).

The database connection is set in `misc/database_auth.php`: host `localhost`, user `root`, empty
password, database `kpi`. These are XAMPP's defaults; change them if your setup differs.

### 4.3 Option B: command line (PHP's built-in server)

From the project folder:

```bash
# 1. Create the database and load the demo data (use `mysql` instead of `mariadb` if needed)
mariadb -u root -e "CREATE DATABASE kpi CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"
mariadb -u root kpi < kpi_data.sql

# 2. Start the app
php -S 127.0.0.1:8766 -t .
```

Then open **http://127.0.0.1:8766/**. The database user must match `misc/database_auth.php`
(`root` with an empty password by default).

> PHP's built-in server ignores `.htaccess` files, so the folders that Apache blocks
> (`turnover_model/`, `migrations/`, and scripts in `uploads/`) are reachable there. Use it only
> for local testing; deploy behind Apache.

### 4.4 Which SQL file?

| File | Use it for |
|---|---|
| `kpi_data.sql` | **Demo and marking.** Schema plus demo accounts, two projects, tasks and ratings. Drops and recreates every table, so use it only on a demo database. |
| `kpi.sql` | **Clean install.** Schema, the four roles and one administrator (`admin` / `Admin@123`; change it after signing in). |
| `migrations/001`–`004` | **Upgrading** a database created by an older version of this app. Run them in order. |

### 4.5 The Turnover Risk page (Python)

The page shows scores that a Python script produces. To generate them:

```bash
python3 -m venv turnover_model/.venv
turnover_model/.venv/bin/pip install numpy pandas scikit-learn matplotlib

turnover_model/.venv/bin/python turnover_model/generate_turnover_data.py   # synthetic data
turnover_model/.venv/bin/python turnover_model/train_turnover_model.py     # train + score
```

Then open **Admin → Extras → Turnover Risk**. Until this has been run, the page explains how to
generate the scores. (On Windows, use `turnover_model\.venv\Scripts\python` instead of
`turnover_model/.venv/bin/python`.)

---

## 5. Demo accounts

These are in `kpi_data.sql`. All accounts sign in at the same page; the app routes administrators
and employees to their own portals. Employee passwords follow the app's rule for new employees
(**password = username**).

| Username | Password | Role | What to try |
|---|---|---|---|
| `admin` | `Admin@123` | Administrator | Everything in the admin portal, including Turnover Risk |
| `jmensah` | `jmensah` | Auditor | Rate tasks on *Order Management System*; close assessment |
| `kboateng` | `kboateng` | General Manager | Rate anyone during assessment; close assessment |
| `aowusu` | `aowusu` | Manager | Rate the staff in Information Systems |
| `petra` | `petra` | Employee | Add tasks with evidence on *Customer Portal Upgrade*; see her own ratings |
| `eaddo` | `eaddo` | Employee | Same as petra |
| `ytetteh` | `ytetteh` | Employee who has **left** | Sign-in is refused; shown as "Left" on the Employees page |

Demo projects:
- **Order Management System** is *under assessment*: tasks are locked and ratings are open.
- **Customer Portal Upgrade** is *open*: employees can add tasks.

---

## 6. How to test

### 6.1 Manual walkthrough (about 15 minutes)

Each step lists what you should see.

**Sign-in and access control**
1. Sign in as `ytetteh` / `ytetteh`. You get "Unauthorized access. Please contact administrator."
   (this person has left).
2. Without signing in, open `/admin/index.php`. You are sent back to the sign-in page.

**Administrator** (`admin` / `Admin@123`)
1. **Employees:** the header shows the number of active staff, and *Yaw Tetteh* shows "Left on 2026-07-31".
2. Create an employee with a **hire date**, then edit them. The changes are saved.
3. Hover over an employee and choose **Mark as Left**: pick a date and a reason. The card now shows
   "Left on …", and that person can no longer sign in. **Reinstate** reverses it.
4. **Roles:** try to delete *Employee*. You see "N employees still have this role…"; roles that are
   in use cannot be deleted.
5. **Create → Project:** people who have left do not appear in the assignment list.
6. **Extras → Turnover Risk:** a "Demo data" banner, the High/Medium/Low counts, a ranked list with
   reasons, risk by department and the drivers. Filter by band and by department.

**Employee** (`petra` / `petra`)
1. Open *Customer Portal Upgrade* and add a task with a PDF attached. It appears in the list.
2. Try to upload a `.php` file renamed to `.png`. The upload is refused (the server checks the real file contents, not just the name).
3. Open *Order Management System*. Tasks are locked, and you see the assessment view.

**Manager** (`aowusu` / `aowusu`)
1. Open *Order Management System*. You can rate Petra's and Esi's tasks (same department).

**Auditor** (`jmensah` / `jmensah`)
1. Rate one of Petra's tasks, then update the rating.
2. **Close Assessment** is visible (it is hidden for employees and managers) and works.

To return to the original demo state, import `kpi_data.sql` again.

### 6.2 Automated test suite

`tests/regression.sh` checks **86 behaviours** through the app's real HTTP endpoints:

- every create/edit/delete action in both portals;
- every admin page loads with no PHP errors;
- access rules for each role (for example, staff cannot edit others' tasks, and only auditors and
  the general manager can close assessment);
- **SQL injection** against the sign-in form, page IDs, search boxes and form fields;
- **file-upload attacks** (PHP disguised as an image) and **stored XSS** in task descriptions.

Run it with the app started (section 4):

```bash
bash tests/regression.sh
```

Expected result: `passed 86, failed 0`. The suite **re-imports `kpi_data.sql`** before and after
running, so only use it on the demo database. Settings: `BASE_URL` (default
`http://127.0.0.1:8766`), `DB_NAME` (default `kpi`), `DB_CLIENT` (default `mariadb -u root`, or
`mysql -u root`). For XAMPP, for example:

```bash
BASE_URL=http://localhost/kpi DB_CLIENT="/Applications/XAMPP/xamppfiles/bin/mysql -u root" bash tests/regression.sh
```

### 6.3 Reproducing the machine-learning results

All scripts use fixed random seeds, so the numbers below are reproduced exactly.

```bash
turnover_model/.venv/bin/python turnover_model/generate_turnover_data.py   # synthetic data
turnover_model/.venv/bin/python turnover_model/train_turnover_model.py     # AUC 0.627, lift 2.0x
turnover_model/.venv/bin/python turnover_model/compare_models.py           # ~1 min  -> results/
turnover_model/.venv/bin/python turnover_model/evaluate_ibm.py             # ~8 min  -> results/ibm/
```

`evaluate_ibm.py` needs `WA_Fn-UseC_-HR-Employee-Attrition.csv` from
[IBM HR Analytics Employee Attrition & Performance](https://www.kaggle.com/datasets/pavansubhasht/ibm-hr-analytics-attrition-dataset)
(free Kaggle account) placed in `turnover_model/`.

---

## 7. The machine-learning model

This section summarises the approach. The full method, with every formula and setting, is in
[`turnover_model/README.md`](turnover_model/README.md).

### 7.1 The problem

> *Given what the system knows about an employee at the end of a quarter, how likely are they to
> resign within the next 6 months?*

This is a **binary classification** problem. The output is a probability between 0 and 1, which is
used to **rank** staff so that HR can check in first with the people most at risk. Only
**voluntary** resignations are predicted: dismissals, retirements and contracts ending have different
causes, so those rows are left out of training.

### 7.2 The data

**One row = one employee at one quarter-end.** It holds about 20 signals, all measured using only
information available on that date:

| Group | Signals |
|---|---|
| From the KPI app | average rating and its trend, self-rating vs assessors' rating, tasks logged vs department average, share of tasks logged late, projects assigned, days since last sign-in |
| HR | tenure, grade, months since last promotion, pay relative to the pay-band midpoint, contract type, sick days, training hours |
| Team | recent manager change, colleagues who left the department recently |

**No sensitive attributes** (age, gender, ethnicity, religion, health) are used, and free-text
comments are not used as inputs.

**Why synthetic data:** real resignation records are personal data, and an organisation has too few
leavers for a student project. `generate_turnover_data.py` simulates about 450 employees over
3 years. A hidden "intention to leave" builds up from realistic causes (stalled promotion, low pay,
a new manager, colleagues leaving) and shows up in the measurable signals before people resign. The
signal is deliberately modest, as in real HR data. To check that the method also works on data I
did not design, it is validated on IBM's public benchmark (section 7.6).

### 7.3 The model: logistic regression

The model gives each signal a weight, adds them up, and turns the total into a probability:

```
z = b0 + b1·x1 + b2·x2 + … + bk·xk        (x = signals, standardised)
p = 1 / (1 + e^(−z))                       (chance of resigning within 6 months)
```

### 7.4 Why logistic regression

1. **Accuracy:** on both datasets it is **as accurate as, or more accurate than,** the more complex
   models tested (section 7.6). It is never significantly worse.
2. **Explainable:** each person's score splits exactly into each signal's contribution
   (`weight × value`), so the risk page can say *why* someone is flagged, e.g. "76% of tasks logged
   late". Tree ensembles can only approximate this.
3. **Suits small data:** with a few hundred leavers, a model with one weight per signal plus a
   penalty is far less likely to memorise noise than a forest of trees.
4. **Honest probabilities:** it is trained directly on log-loss, so "20% risk" means about 20%.
5. **Auditable for fairness:** one visible weight per signal is easy to inspect.
6. **Easy to deploy:** the trained model is just a list of numbers (`turnover_model.json`), which
   PHP can score with one formula, with no Python on the server.

### 7.5 How it is trained (`train_turnover_model.py`)

1. **Label the rows:** `resigned_within_6m` is 1 if the person resigned within 182 days of the
   snapshot. Rows with other kinds of exit are removed.
2. **Split by time, with a gap:** train on Dec 2023 to Mar 2025 and test on Sep 2025 to Mar 2026. The
   6-month gap stops training labels, which look 6 months ahead, from seeing into the test period.
   A random split would leak the future and inflate every score.
3. **Prepare the inputs** using the training rows only:
   - drop signals that are mostly blank;
   - fill blanks with the median, plus a "was missing" flag;
   - turn categories into 0/1 columns;
   - standardise each signal to mean 0 and standard deviation 1.
4. **Handle imbalance:** only about 7% of rows are resignations, so leavers are weighted by
   *stayers ÷ leavers*. A *prior correction* afterwards (subtracting `log w` from the intercept)
   keeps the output a real probability.
5. **Regularise:** a ridge penalty `λ Σ b²` keeps the weights small unless the data supports them.
   λ is chosen by **rolling validation inside the training period** (train on earlier quarters,
   validate on later ones), picking the lowest log-loss. λ = 0.1 was chosen.
6. **Fit:** minimise the weighted log-loss plus the penalty, using **Newton's method**, which
   converges exactly in about 10 steps.
7. **Evaluate once on the test period** against simple baselines (random, an HR rule, the best single
   signal).
8. **Refit on all labelled data, export** `turnover_model.json`, and **score current staff:** the
   High band is the riskiest 10%, Medium the next 20%. Each person gets their top 3 reasons, and a
   drift check warns if overall risk has risen.

### 7.6 Results

**Synthetic KPI data** (held-out test period, 95% bootstrap confidence intervals)

| Model | AUC | Top-10% lift |
|---|---|---|
| **Logistic regression** (chosen) | **0.627** (0.572–0.682) | 2.02× |
| Random forest | 0.660, difference +0.032 (−0.001 to +0.070): not significant | 2.29× |
| Gradient boosting | 0.601 | 2.20× |
| Decision tree | 0.534, significantly worse | 1.67× |
| HR rule "no promotion in 3 years" | 0.513 | 0.97× |

**IBM HR Attrition benchmark** (1,470 employees; nested 10-fold cross-validation)

| Model | AUC, all features | AUC without age, gender and marital status |
|---|---|---|
| **Logistic regression** | **0.833** (0.803–0.863) | 0.825 |
| Gradient boosting | 0.817, not significantly different | 0.810 |
| Random forest | 0.801, significantly worse | 0.795 |
| Decision tree | 0.708, significantly worse | 0.709 |

What this shows:
- **The method works on independent data:** AUC 0.83 on the standard benchmark.
- **Logistic regression is the right choice:** it is best or tied on both datasets.
- **Fairness costs almost nothing:** removing sensitive attributes lowers AUC by only **0.009**.
- **Practical value:** the people flagged as High risk resign at **2×** (synthetic) to **4×** (IBM)
  the average rate.
- **Why the scores differ:** IBM's data includes satisfaction scores and overtime, which are strong
  signals the KPI app does not yet record. Pulse surveys are a natural next step (section 9).

Charts for the report (ROC curves, cumulative gains, calibration, λ tuning, feature importance,
confusion matrix) are in `turnover_model/results/` and `turnover_model/results/ibm/`.

### 7.7 Responsible use

Scores are for starting **supportive conversations**, never for decisions about pay, promotion or
dismissal. Only administrators can see the risk page, every score comes with reasons, sensitive
attributes are excluded, and no real personal data was used to build the model.

---

## 8. Security

Weaknesses found during development and how they were fixed (OWASP Top 10 categories):

| Risk | Fix |
|---|---|
| **A03 Injection:** SQL built from request data | Every query uses prepared statements (`mysqli::execute_query`); search columns are whitelisted |
| **A01 Broken access control** | A sign-in check at the top of every page and endpoint; ownership checks (staff can only change their own tasks; assessors only rate the people they are allowed to; only auditors and the GM can close assessment); the user's identity always comes from the session, never from the request |
| **A04/A05 Unsafe file upload:** a PHP file disguised as an image could run as code | Extension whitelist **and** content check (`finfo`); `uploads/.htaccess` blocks scripts |
| **A03 Stored XSS** | User text (including task descriptions saved with attachments, which used to be stored raw) is HTML-escaped before it is stored |
| **A07 Authentication** | Passwords hashed with bcrypt; a new session ID at sign-in (prevents session fixation); people who have left are signed out on their next request |
| **A02/A09 Information leaks** | No SQL, password hashes, database errors or request data in responses; no `display_errors`; an administrator password that had been left in a code comment was removed |
| Data loss | Employees are marked as left instead of deleted; roles in use cannot be deleted |

The automated suite (section 6.2) tests these protections.

---

## 9. Limitations and future work

- **The model is trained on synthetic data.** Before real use, it needs real exit records (roughly 50+
  resignations), backfilled hire dates, and data-protection approval.
- **History is overwritten** when an employee's role or department changes. A monthly snapshot table
  would preserve it for the model.
- **Not yet built:** a training-data export from the live database, pulse surveys (the strongest
  signal in the IBM data), a check-in workflow for flagged staff, and a separate HR role.
- **Security hardening for production:** CSRF tokens, a password change and reset feature, a stronger
  password policy (new employees' passwords equal their username), two-factor sign-in for
  administrators, consistent escaping when data is displayed, and database credentials moved out of
  `misc/database_auth.php` into server configuration.

---

## 10. Troubleshooting

| Problem | Fix |
|---|---|
| `Access denied for user 'root'@'localhost'` | Your database password is not empty: update `misc/database_auth.php` |
| `Call to undefined method mysqli::execute_query()` | PHP is older than 8.2: upgrade PHP |
| Sign-in says "Login failed" for a demo account | Re-import `kpi_data.sql`; check the database name is `kpi` |
| Turnover Risk says "No risk scores yet" | Run the two Python commands in section 4.5 |
| Turnover Risk shows "Demo data" | Expected: the scores are for synthetic employees, not the people in the database |
| Import error mentioning a collation | Create the database with collation `utf8mb4_general_ci` before importing |
| `tests/regression.sh` says the app is not reachable | Start the app first, or set `BASE_URL` to where it runs |
| Port 8766 is already in use | Use another port, e.g. `php -S 127.0.0.1:8080 -t .`, and set `BASE_URL` to match |
