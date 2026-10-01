"""Sanity checks for the synthetic turnover data: leakage, signal, learnability."""
import numpy as np, pandas as pd
e = pd.read_csv('employees.csv', parse_dates=['hire_date', 'exit_date'])
t = pd.read_csv('turnover_train.csv', parse_dates=['snapshot_date'])
c = pd.read_csv('turnover_score_current.csv')
t['exit_type_within_6m'] = t.exit_type_within_6m.fillna('')

m = t.merge(e[['employee_id', 'exit_date', 'hire_date']], on='employee_id')
print('rows after exit:', int((m.snapshot_date >= m.exit_date).sum()), '| rows before hire:', int((m.snapshot_date < m.hire_date).sum()),
      '| active GMs now:', int((c.role == 'General Manager').sum()), '| leavers in current file:', int(c.employee_id.isin(e[e.status == 'left'].employee_id).sum()))
print('exits:', e.exit_type.value_counts().to_dict())
print('headcount:', list(t.groupby('snapshot_date').size()), '-> now', len(c))
yearly = e[(e.exit_type == 'resigned')].exit_date.dt.year.value_counts().sort_index().to_dict()
print('resignations by year:', yearly, '| avg headcount', int(t.groupby('snapshot_date').size().mean()))

feats = ['avg_rating', 'rating_change', 'tasks_vs_dept_avg', 'late_task_pct', 'sick_days_q', 'months_since_promotion',
         'compa_ratio', 'manager_changed_6m', 'team_exits_6m', 'self_vs_manager_rating_gap', 'tenure_months', 'days_since_last_login']
print(t.groupby('resigned_within_6m')[feats].mean().T.round(2).rename(columns={0: 'stayed', 1: 'resigned<6m'}))
print('6m resignation rate by dept:', t.groupby('department').resigned_within_6m.mean().round(3).sort_values().to_dict())
t['early'] = t.tenure_months < 18
print('6m resignation rate, tenure<18m vs rest:', t.groupby('early').resigned_within_6m.mean().round(3).to_dict())

df = t[(t.exit_type_within_6m == '') | (t.resigned_within_6m == 1)].copy()   # voluntary exits only
# days_since_last_login only exists from Jul 2025, so it can't be learned from older quarters
model_feats = [f for f in feats if f != 'days_since_last_login']
X = pd.get_dummies(df[model_feats + ['department', 'contract_type', 'grade']], columns=['department', 'contract_type'], drop_first=True).astype(float)
X = X.fillna(X.median())
y = df.resigned_within_6m.values
train = (df.snapshot_date <= '2025-06-30').values
mu, sd = X[train].mean(), X[train].std().replace(0, 1)
Xs = np.c_[np.ones(len(X)), ((X - mu) / sd).values]
w = np.zeros(Xs.shape[1])
for _ in range(3000):
    p = 1 / (1 + np.exp(-Xs[train] @ w))
    w -= 0.1 * (Xs[train].T @ (p - y[train]) / train.sum() + 0.01 * np.r_[0, w[1:]])
def auc(score, label):
    r = pd.Series(score).rank().values; pos = label == 1
    return (r[pos].sum() - pos.sum() * (pos.sum() + 1) / 2) / (pos.sum() * (~pos).sum())
ptest = 1 / (1 + np.exp(-Xs[~train] @ w))
top = np.argsort(-ptest)[: int(0.1 * len(ptest))]
print(f'logistic regression, trained <= 2025-06, tested after: AUC {auc(ptest, y[~train]):.3f}; '
      f'top 10% flagged resign at {y[~train][top].mean():.2f} vs base {y[~train].mean():.2f}')
print('top weights:', pd.Series(w[1:], index=X.columns).sort_values(key=abs, ascending=False).head(8).round(2).to_dict())
