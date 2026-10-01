"""
Synthetic employee turnover data for the KPI app's risk model.

Everything here is made up. No real people, names or contact details.

Each active employee gets one row per quarter-end snapshot. A hidden "intention to
leave" builds up from realistic causes (stalled promotion, pay below band, a new
manager, colleagues leaving) and leaks into the observable signals (ratings fall,
fewer tasks, more late tasks, more sick days, fewer logins) BEFORE the person
resigns -- which is what a real model has to pick up on.

Outputs (written next to this script):
  employees.csv               one row per person, mirrors the app's employee + employee_exit tables
  turnover_train.csv          labelled snapshots (label fully observable: 6 months of future known)
  turnover_score_current.csv  latest snapshot, no label -- "who is at risk right now"
  data_dictionary.csv         what every column means

Run:  python3 generate_turnover_data.py [--employees 450] [--seed 42]
"""
import argparse
from pathlib import Path

import numpy as np
import pandas as pd

QUARTER_ENDS = pd.to_datetime([
    "2023-12-31", "2024-03-31", "2024-06-30", "2024-09-30",
    "2024-12-31", "2025-03-31", "2025-06-30", "2025-09-30",
    "2025-12-31", "2026-03-31", "2026-06-30", "2026-09-30",
])
LABEL_HORIZON_DAYS = 182          # "resigned within the next 6 months"
LAST_LOGIN_TRACKED_FROM = pd.Timestamp("2025-07-01")  # older snapshots predate the last_login column

DEPARTMENTS = {
    # name: (share of headcount, extra resignation pressure)
    "Finance": (0.14, 0.0),
    "Operations": (0.22, 0.15),
    "IT": (0.12, 0.35),             # skills in demand elsewhere
    "Marketing": (0.10, 0.10),
    "Human Resources": (0.07, -0.10),
    "Sales": (0.20, 0.45),          # classic high-turnover function
    "Audit": (0.08, -0.15),
    "Legal": (0.07, -0.20),
}
EXIT_TYPES = ["resigned", "dismissed", "retired", "contract_ended"]


def sigmoid(x):
    return 1 / (1 + np.exp(-x))


class Org:
    def __init__(self, n_employees, rng):
        self.rng = rng
        self.people = []          # dicts, including hidden state (prefixed with _)
        self.next_id = 1
        self.dept_names = list(DEPARTMENTS)
        self.dept_shares = np.array([DEPARTMENTS[d][0] for d in self.dept_names])
        self.dept_manager_changed = {d: -99 for d in self.dept_names}  # quarter index of last change
        self.exit_log = []        # (quarter_index, department)
        self.pending_hires = []   # (hire_date, department, role) waiting for their start date

        start = QUARTER_ENDS[0]
        for _ in range(n_employees):
            tenure_months = int(min(rng.gamma(1.6, 50), 420))
            self.hire(start - pd.DateOffset(months=tenure_months), start)
        self.assign_leadership()

    # ----- people -------------------------------------------------------------
    def hire(self, hire_date, as_of, department=None):
        rng = self.rng
        tenure_years = (as_of - hire_date).days / 365.25
        department = department or rng.choice(self.dept_names, p=self.dept_shares)
        grade = int(np.clip(1 + tenure_years // 5 + rng.integers(-1, 2), 1, 6))
        fixed_term = rng.random() < (0.35 if tenure_years < 2 else 0.12)
        person = {
            "employee_id": f"E{self.next_id:04d}",
            "department": department,
            "role": "Staff",
            "hire_date": hire_date.normalize(),
            "contract_type": "fixed_term" if fixed_term else "permanent",
            "grade": grade,
            "status": "active",
            "exit_date": pd.NaT,
            "exit_type": "",
            # hidden state -- never exported
            "_ability": rng.normal(0, 1),
            "_baseline_satisfaction": rng.normal(0, 1),
            "_intent": rng.normal(0.1, 0.6),
            "_age_at_hire": max(20.0, float(np.clip(rng.normal(36 + 0.5 * tenure_years, 8), 22, 63)) - tenure_years),
            "_months_since_promotion": float(rng.integers(0, int(max(6, tenure_years * 12)) + 1) if tenure_years > 0.5 else tenure_years * 12),
            "_compa_ratio": rng.normal(1.0, 0.08),
            "_contract_end": (as_of + pd.DateOffset(months=int(rng.integers(3, 25)))) if fixed_term else pd.NaT,
            "_prev_rating": np.nan,
        }
        self.next_id += 1
        self.people.append(person)
        return person

    def assign_leadership(self):
        active = [p for p in self.people if p["status"] == "active"]
        # one General Manager, a few auditors, roughly one manager per 10 staff
        gm = max(active, key=lambda p: (p["grade"], p["_ability"]))
        gm["role"] = "General Manager"
        for dept in self.dept_names:
            members = [p for p in active if p["department"] == dept and p["role"] == "Staff"]
            members.sort(key=lambda p: (p["grade"], p["_ability"]), reverse=True)
            for p in members[: max(1, len(members) // 10)]:
                p["role"] = "Manager"
        audit_staff = [p for p in active if p["department"] == "Audit" and p["role"] == "Staff"]
        for p in audit_staff[:6]:
            p["role"] = "Auditor"

    def active(self):
        return [p for p in self.people if p["status"] == "active"]

    def team_exits_last_6m(self, q, dept):
        return sum(1 for (eq, d) in self.exit_log if d == dept and q - 2 < eq <= q)

    # ----- one quarter ----------------------------------------------------------
    def snapshot_and_advance(self, q):
        """Record every active employee as of QUARTER_ENDS[q], then simulate the next quarter."""
        rng = self.rng
        as_of = QUARTER_ENDS[q]
        rows = []
        # replacements whose start date has arrived join before the snapshot
        for hire_date, dept, role in [h for h in self.pending_hires if h[0] <= as_of]:
            self.hire(hire_date, hire_date, department=dept)["role"] = role
        self.pending_hires = [h for h in self.pending_hires if h[0] > as_of]
        active = self.active()

        # Department manager changes (a manager left, or a reshuffle)
        for dept in self.dept_names:
            if rng.random() < 0.06:
                self.dept_manager_changed[dept] = q

        dept_size = pd.Series([p["department"] for p in active]).value_counts().to_dict()

        # Update hidden intent, then derive observable signals from it
        for p in active:
            tenure_m = (as_of - p["hire_date"]).days / 30.44
            manager_changed = q - self.dept_manager_changed[p["department"]] <= 1
            team_exits = self.team_exits_last_6m(q, p["department"])
            pressure = (
                0.45 * min(2.0, max(0, (p["_months_since_promotion"] - 30) / 24))  # stalled career
                + 7.0 * max(0, 0.96 - p["_compa_ratio"])                           # underpaid vs band
                + 0.80 * manager_changed
                + 3.0 * team_exits / dept_size[p["department"]]                     # contagion (share of team gone)
                + 0.60 * (tenure_m < 18)                                            # early-tenure churn
                - 0.50 * (tenure_m > 120)                                           # long-timers settle
                - 0.30 * p["_baseline_satisfaction"]
                + DEPARTMENTS[p["department"]][1]
            )
            p["_intent"] = 0.65 * p["_intent"] + 0.35 * pressure + rng.normal(0, 0.25)
            intent, ability = p["_intent"], p["_ability"]

            rating = float(np.clip(62 + 11 * ability - 7 * intent + rng.normal(0, 7), 10, 100))
            tasks = int(rng.poisson(max(1.0, 12 * np.exp(0.12 * ability - 0.22 * intent))))
            late_pct = float(np.clip(sigmoid(-1.6 + 0.55 * intent - 0.35 * ability + rng.normal(0, 0.35)), 0, 1))
            sick_days = int(rng.poisson(1.4 * np.exp(0.35 * max(intent, -1))))
            login_gap = float(np.round(rng.exponential(1.2 * np.exp(0.8 * max(intent, -1))) + 0.5, 1))
            self_gap = float(np.round(6 + 5 * intent + rng.normal(0, 6), 1))

            rows.append({
                "snapshot_date": as_of.date().isoformat(),
                "employee_id": p["employee_id"],
                "department": p["department"],
                "role": p["role"],
                "contract_type": p["contract_type"],
                "grade": p["grade"],
                "tenure_months": int(round(tenure_m)),
                "months_since_promotion": int(round(p["_months_since_promotion"])),
                "compa_ratio": round(p["_compa_ratio"], 3),
                "manager_changed_6m": int(manager_changed),
                "team_exits_6m": team_exits,
                "avg_rating": round(rating, 1),
                "rating_change": round(rating - p["_prev_rating"], 1) if not np.isnan(p["_prev_rating"]) else np.nan,
                "self_vs_manager_rating_gap": self_gap,
                "tasks_logged": tasks,
                "late_task_pct": round(late_pct, 3),
                "projects_assigned": int(rng.poisson(1.8)) + 1,
                "sick_days_q": sick_days,
                "training_hours_12m": int(rng.gamma(2, 6 * np.exp(0.2 * ability))),
                "days_since_last_login": login_gap if as_of >= LAST_LOGIN_TRACKED_FROM else np.nan,
            })
            p["_prev_rating"] = rating
            p["_last_rating"] = rating

        # tasks relative to department peers this quarter
        df = pd.DataFrame(rows)
        if not df.empty:
            df["tasks_vs_dept_avg"] = (df["tasks_logged"] / df.groupby("department")["tasks_logged"].transform("mean")).round(2)

        # ---- simulate the following quarter: exits, promotions, pay, hires ----
        if q + 1 < len(QUARTER_ENDS):
            next_end = QUARTER_ENDS[q + 1]
            leavers = []
            for p in active:
                tenure_m = (as_of - p["hire_date"]).days / 30.44
                age = p["_age_at_hire"] + tenure_m / 12
                exit_type = None
                if age >= 60 and rng.random() < 0.35:
                    exit_type = "retired"
                elif p["contract_type"] == "fixed_term" and pd.notna(p["_contract_end"]) and p["_contract_end"] <= next_end:
                    if rng.random() < (0.55 if p["_last_rating"] < 50 else 0.2):
                        exit_type = "contract_ended"
                    else:
                        p["_contract_end"] = p["_contract_end"] + pd.DateOffset(months=12)
                elif rng.random() < sigmoid(-6.2 + 0.06 * max(0, 45 - p["_last_rating"])):
                    exit_type = "dismissed"
                elif p["role"] != "General Manager" and rng.random() < sigmoid(-4.4 + 1.6 * p["_intent"]):
                    exit_type = "resigned"

                if exit_type:
                    days_into_quarter = int(rng.integers(1, (next_end - as_of).days + 1))
                    p["status"] = "left"
                    p["exit_type"] = exit_type
                    p["exit_date"] = as_of + pd.Timedelta(days=days_into_quarter)
                    self.exit_log.append((q + 1, p["department"]))
                    leavers.append(p)
                    continue

                # promotions favour strong, long-waiting people
                p["_months_since_promotion"] += 3
                promo_chance = sigmoid(-4.3 + 0.7 * p["_ability"] + 0.03 * p["_months_since_promotion"] - 0.4 * p["grade"] + 1.2)
                if p["grade"] < 6 and rng.random() < promo_chance:
                    p["grade"] += 1
                    p["_months_since_promotion"] = 0
                    p["_compa_ratio"] = rng.normal(0.93, 0.04)
                    p["_intent"] -= 0.5
                # pay drifts down against the market; annual review each January
                p["_compa_ratio"] -= 0.006
                if next_end.month == 3:
                    p["_compa_ratio"] += 0.024 + 0.02 * (p["_last_rating"] - 60) / 20 + rng.normal(0, 0.01)

            # backfill almost every leaver after a 1-3 month vacancy, plus a little growth
            for p in leavers:
                if rng.random() < 0.95:
                    hire_date = p["exit_date"] + pd.Timedelta(days=int(rng.integers(30, 100)))
                    self.pending_hires.append((hire_date, p["department"], p["role"]))
            for _ in range(rng.poisson(2)):
                hire_date = as_of + pd.Timedelta(days=int(rng.integers(1, 90)))
                self.pending_hires.append((hire_date, rng.choice(self.dept_names, p=self.dept_shares), "Staff"))

        return df


def add_labels(snapshots, employees):
    exits = employees.set_index("employee_id")[["exit_date", "exit_type"]]
    df = snapshots.join(exits, on="employee_id")
    snap = pd.to_datetime(df["snapshot_date"])
    within = df["exit_date"].notna() & (df["exit_date"] > snap) & (df["exit_date"] <= snap + pd.Timedelta(days=LABEL_HORIZON_DAYS))
    df["resigned_within_6m"] = (within & (df["exit_type"] == "resigned")).astype(int)
    df["exit_type_within_6m"] = np.where(within, df["exit_type"], "")
    return df.drop(columns=["exit_date", "exit_type"])


DICTIONARY = [
    ("snapshot_date", "When the row was measured (quarter end). Features use only information up to this date."),
    ("employee_id", "Anonymous ID. No names or contact details anywhere in the data."),
    ("department", "Department at snapshot time."),
    ("role", "Staff / Manager / Auditor / General Manager (same roles as the app)."),
    ("contract_type", "permanent or fixed_term."),
    ("grade", "Pay/seniority grade 1 (junior) to 6 (senior)."),
    ("tenure_months", "Months since hire_date."),
    ("months_since_promotion", "Months since last grade increase (or hire). Long stalls raise risk."),
    ("compa_ratio", "Salary divided by midpoint of their pay band. Below ~0.92 = paid under peers."),
    ("manager_changed_6m", "1 if the department manager changed in the last 6 months."),
    ("team_exits_6m", "Departures from the same department in the last 6 months."),
    ("avg_rating", "Average task rating this quarter (10-100, as in the app's assessments)."),
    ("rating_change", "avg_rating minus last quarter's. Blank on an employee's first snapshot."),
    ("self_vs_manager_rating_gap", "Self rating minus manager/auditor rating. Large gaps suggest feeling undervalued."),
    ("tasks_logged", "Tasks logged this quarter."),
    ("tasks_vs_dept_avg", "tasks_logged divided by the department average (1.0 = typical)."),
    ("late_task_pct", "Share of tasks logged after their deadline (0-1)."),
    ("projects_assigned", "Projects assigned this quarter."),
    ("sick_days_q", "Sick days this quarter."),
    ("training_hours_12m", "Training hours received in the last 12 months."),
    ("days_since_last_login", "Days since last app sign-in at snapshot time. Blank before Jul 2025 (not yet tracked) -- realistic missing data."),
    ("resigned_within_6m", "LABEL. 1 if the employee resigned within 182 days after snapshot_date. Not a feature."),
    ("exit_type_within_6m", "OUTCOME, not a feature. Any exit type in the next 6 months (resigned/dismissed/retired/contract_ended). Use to exclude or study non-voluntary exits."),
]


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--employees", type=int, default=450)
    parser.add_argument("--seed", type=int, default=42)
    args = parser.parse_args()
    rng = np.random.default_rng(args.seed)
    out = Path(__file__).resolve().parent

    org = Org(args.employees, rng)
    snapshots = pd.concat([org.snapshot_and_advance(q) for q in range(len(QUARTER_ENDS))], ignore_index=True)

    employees = pd.DataFrame([{k: v for k, v in p.items() if not k.startswith("_")} for p in org.people])
    labelled = add_labels(snapshots, employees)

    # A label is only trustworthy once 6 months of future are known
    data_end = QUARTER_ENDS[-1]
    snap = pd.to_datetime(labelled["snapshot_date"])
    train = labelled[snap + pd.Timedelta(days=LABEL_HORIZON_DAYS) <= data_end]
    current = labelled[snap == data_end].drop(columns=["resigned_within_6m", "exit_type_within_6m"])

    employees["hire_date"] = employees["hire_date"].dt.date
    employees["exit_date"] = employees["exit_date"].dt.date
    employees.to_csv(out / "employees.csv", index=False)
    train.to_csv(out / "turnover_train.csv", index=False)
    current.to_csv(out / "turnover_score_current.csv", index=False)
    pd.DataFrame(DICTIONARY, columns=["column", "description"]).to_csv(out / "data_dictionary.csv", index=False)

    print(f"employees.csv               {len(employees):>6} people ({(employees.status == 'left').sum()} left)")
    print(f"turnover_train.csv          {len(train):>6} rows, {train.snapshot_date.min()} .. {train.snapshot_date.max()}")
    print(f"turnover_score_current.csv  {len(current):>6} rows, {data_end.date()}")


if __name__ == "__main__":
    main()
