<?php
    require_once '../misc/admin_login_required.php';
    require_once '../misc/database_auth.php';

    // The model is trained, and current staff are scored, offline by
    // turnover_model/train_turnover_model.py. This page only displays the results.
    $modelDir = __DIR__ . '/../turnover_model';
    $modelFile = "$modelDir/turnover_model.json";
    $scoresFile = "$modelDir/risk_scores.csv";

    function riskEscape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    function riskPercent($probability)
    {
        return number_format($probability * 100, 1) . '%';
    }

    $model = is_readable($modelFile) ? json_decode(file_get_contents($modelFile), true) : null;
    $scores = [];
    if (is_readable($scoresFile) && ($handle = fopen($scoresFile, 'r'))) {
        $header = fgetcsv($handle, null, ',', '"', '');
        while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
            if ($header && count($row) == count($header)) {
                $row = array_combine($header, $row);
                $row['risk_probability'] = (float)$row['risk_probability'];
                $scores[] = $row;
            }
        }
        fclose($handle);
    }

    // Real exports use employee.id; the synthetic demo uses IDs like E0604 that match nobody
    $employees = [];
    $numericIds = array_filter(array_column($scores, 'employee_id'), 'ctype_digit');
    if ($numericIds) {
        $idList = implode(',', array_map('intval', $numericIds));
        $result = $con->query("SELECT id, CONCAT_WS(' ', surname, other_names) AS name, status FROM employee WHERE id IN ($idList)");
        while ($row = $result->fetch_assoc()) {
            $employees[$row['id']] = $row;
        }
    }
    $isDemo = $scores && !$employees;
    // Anyone marked as left since the scores were produced drops off the lists
    $scores = array_values(array_filter($scores, function ($row) use ($employees) {
        return ($employees[$row['employee_id']]['status'] ?? 'active') != 'left';
    }));
    usort($scores, fn($a, $b) => $b['risk_probability'] <=> $a['risk_probability']);

    // Summary numbers
    $bands = ['High', 'Medium', 'Low'];
    $bandCounts = array_fill_keys($bands, 0);
    foreach ($scores as $row) {
        if (isset($bandCounts[$row['risk_band']])) {
            $bandCounts[$row['risk_band']]++;
        }
    }
    $currentMean = $scores ? array_sum(array_column($scores, 'risk_probability')) / count($scores) : 0;
    $historicalMean = $model['risk_bands']['historical_mean_probability'] ?? null;
    $isDrifting = $historicalMean && $currentMean > 1.25 * $historicalMean;
    $scoredAt = is_readable($scoresFile) ? filemtime($scoresFile) : null;
    $scoresAgeDays = $scoredAt ? (int)floor((time() - $scoredAt) / 86400) : null;

    $testMetrics = $model['test_metrics'] ?? [];
    $precisionKey = current(array_filter(array_keys($testMetrics), fn($key) => strpos($key, 'precision_top_') === 0));
    $lift = ($precisionKey && !empty($testMetrics['base_rate'])) ? $testMetrics[$precisionKey] / $testMetrics['base_rate'] : null;
    $flaggedShare = isset($model['risk_bands']['high_share']) ? round($model['risk_bands']['high_share'] * 100) : 10;

    // Filters (whitelisted)
    $departments = array_values(array_unique(array_column($scores, 'department')));
    sort($departments);
    $band = in_array($_GET['band'] ?? '', array_merge($bands, ['All']), true) ? $_GET['band'] : 'High';
    $department = in_array($_GET['dept'] ?? '', $departments, true) ? $_GET['dept'] : '';
    $listed = array_values(array_filter($scores, function ($row) use ($band, $department) {
        return ($band == 'All' || $row['risk_band'] == $band) && (!$department || $row['department'] == $department);
    }));
    $maxProbability = $scores ? $scores[0]['risk_probability'] : 1;

    // Risk by department
    $departmentSummary = [];
    foreach ($scores as $row) {
        $summary = &$departmentSummary[$row['department']];
        $summary['staff'] = ($summary['staff'] ?? 0) + 1;
        $summary['high'] = ($summary['high'] ?? 0) + ($row['risk_band'] == 'High' ? 1 : 0);
        $summary['total'] = ($summary['total'] ?? 0) + $row['risk_probability'];
        unset($summary);
    }
    foreach ($departmentSummary as $name => $summary) {
        $departmentSummary[$name]['average'] = $summary['total'] / $summary['staff'];
    }
    uasort($departmentSummary, fn($a, $b) => $b['average'] <=> $a['average']);
    $maxDepartmentAverage = $departmentSummary ? max(array_column($departmentSummary, 'average')) : 1;

    // What drives risk, in plain words
    $featureLabels = [
        'late_task_pct' => 'Tasks logged late',
        'self_vs_manager_rating_gap' => 'Rates self above assessors',
        'sick_days_q' => 'Sick days',
        'manager_changed_6m' => 'Recent manager change',
        'tasks_vs_dept_avg' => 'Tasks logged vs department average',
        'avg_rating' => 'Average rating',
        'rating_change' => 'Rating trend',
        'compa_ratio' => 'Pay vs pay-band midpoint',
        'months_since_promotion' => 'Months since promotion',
        'team_exits_6m' => 'Colleagues leaving the department',
        'tenure_months' => 'Time with the organisation',
        'grade' => 'Grade',
        'projects_assigned' => 'Projects assigned',
        'training_hours_12m' => 'Training received',
        'days_since_last_login' => 'Days since last sign-in',
    ];
    $drivers = [];
    foreach ($model['weights'] ?? [] as $column => $weight) {
        if (substr($column, -9) == '__missing') {
            continue;   // technical "value was blank" flags
        }
        if (strpos($column, '=') !== false) {
            [$field, $value] = explode('=', $column, 2);
            $label = ucfirst(str_replace('_', ' ', $field)) . ": $value";
        } else {
            $label = $featureLabels[$column] ?? $column;
        }
        $drivers[] = ['label' => $label, 'weight' => (float)$weight];
    }
    $raising = array_filter($drivers, fn($d) => $d['weight'] > 0);
    $lowering = array_filter($drivers, fn($d) => $d['weight'] < 0);
    usort($raising, fn($a, $b) => $b['weight'] <=> $a['weight']);
    usort($lowering, fn($a, $b) => $a['weight'] <=> $b['weight']);
    $raising = array_slice($raising, 0, 5);
    $lowering = array_slice($lowering, 0, 5);
?>

<?php
    $pageTitle = "NLA KPI Admin | Turnover Risk";
    require_once './admin_navbar.php';
?>

<style>
    .dashboard-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1.2rem;
        border-bottom: 2px solid rgba(108, 99, 255, 0.08);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }

    .page-header h1 i {
        color: #6c63ff;
    }

    .page-header .subtitle {
        color: #6b7280;
        margin: 0.3rem 0 0 0;
        font-size: 0.9rem;
    }

    .header-badge {
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        padding: 0.5rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .risk-notice {
        display: flex;
        gap: 0.8rem;
        align-items: flex-start;
        padding: 1rem 1.2rem;
        border-radius: 16px;
        margin-bottom: 1rem;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .risk-notice i {
        font-size: 1.2rem;
        margin-top: 0.1rem;
    }

    .risk-notice.demo {
        background: #eef0ff;
        color: #3f3a99;
    }

    .risk-notice.warning {
        background: #fff7e6;
        color: #8a5a00;
    }

    .risk-notice code {
        color: inherit;
        background: rgba(0, 0, 0, 0.06);
        padding: 0.1rem 0.4rem;
        border-radius: 6px;
    }

    .risk-section {
        background: white;
        border-radius: 24px;
        padding: 1.6rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
        margin-bottom: 1.5rem;
    }

    .risk-section h3 {
        font-size: 1.15rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0 0 1.2rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .risk-section h3 i {
        color: #6c63ff;
    }

    .risk-section .section-note {
        color: #6b7280;
        font-size: 0.85rem;
        margin: -0.8rem 0 1.2rem 0;
    }

    /* Stat cards */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1.2rem 1.4rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
        text-decoration: none;
        color: inherit;
        display: block;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    a.stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        color: inherit;
    }

    .stat-card .stat-label {
        color: #6b7280;
        font-size: 0.82rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin: 0;
    }

    .stat-card .stat-value {
        font-size: 1.9rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0.2rem 0 0 0;
        line-height: 1.2;
    }

    .stat-card .stat-sub {
        color: #6b7280;
        font-size: 0.82rem;
        margin: 0.2rem 0 0 0;
    }

    .stat-card.high .stat-value { color: #dc2626; }
    .stat-card.medium .stat-value { color: #d97706; }
    .stat-card.low .stat-value { color: #059669; }
    .stat-card.selected { outline: 2px solid #6c63ff; }

    /* Bands */
    .band-badge {
        display: inline-block;
        padding: 0.2rem 0.75rem;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .band-badge.High { background: #fee2e2; color: #b91c1c; }
    .band-badge.Medium { background: #fef3c7; color: #b45309; }
    .band-badge.Low { background: #d1fae5; color: #047857; }

    /* Filters */
    .risk-filters {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 1rem;
    }

    .risk-filters .filter-pill {
        padding: 0.35rem 1rem;
        border-radius: 50px;
        background: #f0f2f5;
        color: #4b5563;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
    }

    .risk-filters .filter-pill.active {
        background: #6c63ff;
        color: white;
    }

    .risk-filters select {
        border-radius: 50px;
        border: 1px solid #e5e7eb;
        padding: 0.35rem 1rem;
        font-size: 0.85rem;
        max-width: 100%;
    }

    /* Risk list */
    .risk-table {
        width: 100%;
        border-collapse: collapse;
    }

    .risk-table th {
        color: #6b7280;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 600;
        padding: 0.6rem 0.5rem;
        border-bottom: 1px solid #eef0f3;
        text-align: left;
    }

    .risk-table td {
        padding: 0.85rem 0.5rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
        font-size: 0.92rem;
    }

    .risk-table .employee-name {
        font-weight: 600;
        color: #1a1a2e;
        text-decoration: none;
    }

    .risk-table .employee-meta {
        color: #6b7280;
        font-size: 0.82rem;
    }

    .risk-meter {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        min-width: 140px;
    }

    .risk-meter .track {
        flex: 1;
        height: 8px;
        background: #f0f2f5;
        border-radius: 50px;
        overflow: hidden;
    }

    .risk-meter .fill {
        height: 100%;
        border-radius: 50px;
        background: linear-gradient(90deg, #f59e0b, #ef4444);
    }

    .risk-meter .value {
        font-weight: 700;
        min-width: 3.4rem;
        text-align: right;
    }

    .reason-chip {
        display: inline-block;
        background: #f5f3ff;
        color: #4c1d95;
        border-radius: 10px;
        padding: 0.2rem 0.6rem;
        font-size: 0.82rem;
        margin: 0 0.3rem 0.3rem 0;
    }

    /* Department bars */
    .department-row {
        display: grid;
        grid-template-columns: minmax(110px, 160px) 1fr auto;
        gap: 0.8rem;
        align-items: center;
        padding: 0.45rem 0;
        font-size: 0.9rem;
    }

    .department-row .track {
        height: 10px;
        background: #f0f2f5;
        border-radius: 50px;
        overflow: hidden;
    }

    .department-row .fill {
        height: 100%;
        background: #6c63ff;
        border-radius: 50px;
    }

    .department-row .numbers {
        color: #6b7280;
        font-size: 0.82rem;
        white-space: nowrap;
    }

    /* Drivers */
    .driver-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .driver-list li {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.45rem 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.9rem;
    }

    .driver-list .odds.up { color: #b91c1c; font-weight: 600; }
    .driver-list .odds.down { color: #047857; font-weight: 600; }

    .driver-heading {
        font-size: 0.85rem;
        font-weight: 700;
        color: #4b5563;
        margin: 0 0 0.4rem 0;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 3rem;
        color: #d1d5db;
    }

    .ethics-note {
        color: #6b7280;
        font-size: 0.85rem;
        text-align: center;
        margin: 0.5rem 0 2rem 0;
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 1.2rem 1rem;
        }

        .page-header h1 {
            font-size: 1.5rem;
        }

        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.7rem;
        }

        .stat-card {
            padding: 1rem;
        }

        .stat-card .stat-value {
            font-size: 1.5rem;
        }

        .risk-section {
            padding: 1.1rem;
            border-radius: 18px;
        }

        .risk-table thead {
            display: none;
        }

        .risk-table tr {
            display: block;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eef0f3;
        }

        .risk-table td {
            display: block;
            border: none;
            padding: 0.2rem 0;
        }

        .risk-table td.rank {
            display: none;
        }

        .department-row {
            grid-template-columns: 1fr auto;
        }

        .department-row .track {
            grid-column: 1 / -1;
            grid-row: 2;
        }
    }
</style>

<div class="dashboard-wrapper">
    <div class="page-header">
        <div>
            <h1><i class="bi bi-graph-down-arrow"></i> Turnover Risk</h1>
            <p class="subtitle">
                Who may be thinking of leaving in the next 6 months, and why
                <?php if ($scoredAt) { ?>
                    &middot; scored <?php echo riskEscape(date('j M Y', $scoredAt)); ?>
                <?php } ?>
            </p>
        </div>
        <?php if ($scores) { ?>
            <span class="header-badge"><i class="bi bi-people-fill"></i> <?php echo count($scores); ?> staff scored</span>
        <?php } ?>
    </div>

    <?php if (!$model || !$scores) { ?>
        <div class="risk-section">
            <div class="empty-state">
                <i class="bi bi-graph-down-arrow"></i>
                <h4 class="mt-3">No risk scores yet</h4>
                <p>Train the model and score current staff, then reload this page:</p>
                <p><code>python3 turnover_model/train_turnover_model.py</code></p>
            </div>
        </div>
    <?php } else { ?>

        <?php if ($isDemo) { ?>
            <div class="risk-notice demo">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    <strong>Demo data.</strong> These scores are for synthetic employees made by
                    <code>generate_turnover_data.py</code>, not your staff. Real names appear here once the model is
                    trained and run on data exported from this system.
                </div>
            </div>
        <?php } ?>

        <?php if ($isDrifting) { ?>
            <div class="risk-notice warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Overall risk is higher than usual:</strong> <?php echo riskPercent($currentMean); ?> on average now,
                    against <?php echo riskPercent($historicalMean); ?> in the training history. That usually points to an
                    organisation-wide cause (pay, restructuring, workload) worth looking at before individual cases.
                </div>
            </div>
        <?php } ?>

        <?php if ($scoresAgeDays !== null && $scoresAgeDays > 120) { ?>
            <div class="risk-notice warning">
                <i class="bi bi-clock-history"></i>
                <div>
                    <strong>These scores are <?php echo $scoresAgeDays; ?> days old.</strong>
                    Re-run <code>train_turnover_model.py</code> with fresh data so the lists reflect the current quarter.
                </div>
            </div>
        <?php } ?>

        <!-- Summary -->
        <div class="stat-grid">
            <?php foreach ($bands as $bandName) { ?>
                <a class="stat-card <?php echo strtolower($bandName); ?> <?php echo $band == $bandName ? 'selected' : ''; ?>"
                   href="?<?php echo riskEscape(http_build_query(['band' => $bandName, 'dept' => $department])); ?>">
                    <p class="stat-label"><?php echo $bandName; ?> risk</p>
                    <p class="stat-value"><?php echo $bandCounts[$bandName]; ?></p>
                    <p class="stat-sub">
                        <?php echo $bandName == 'High' ? "Riskiest {$flaggedShare}% of staff" : ($bandName == 'Medium' ? 'Worth keeping an eye on' : 'No strong warning signs'); ?>
                    </p>
                </a>
            <?php } ?>
            <div class="stat-card">
                <p class="stat-label">Model accuracy</p>
                <p class="stat-value"><?php echo $lift ? number_format($lift, 1) . '&times;' : '&ndash;'; ?></p>
                <p class="stat-sub">
                    <?php if ($lift) { ?>
                        better than chance at spotting leavers (AUC <?php echo riskEscape(number_format($testMetrics['auc'] ?? 0, 2)); ?>)
                    <?php } else { ?>
                        Not measured
                    <?php } ?>
                </p>
            </div>
        </div>

        <!-- Who is at risk -->
        <div class="risk-section">
            <h3><i class="bi bi-person-exclamation"></i> Who is at risk</h3>
            <div class="risk-filters">
                <?php foreach (array_merge($bands, ['All']) as $bandName) { ?>
                    <a class="filter-pill <?php echo $band == $bandName ? 'active' : ''; ?>"
                       href="?<?php echo riskEscape(http_build_query(['band' => $bandName, 'dept' => $department])); ?>">
                        <?php echo $bandName; ?>
                    </a>
                <?php } ?>
                <form method="get" class="ms-auto">
                    <input type="hidden" name="band" value="<?php echo riskEscape($band); ?>">
                    <select name="dept" onchange="this.form.submit()" aria-label="Filter by department">
                        <option value="">All departments</option>
                        <?php foreach ($departments as $departmentName) { ?>
                            <option value="<?php echo riskEscape($departmentName); ?>" <?php echo $department == $departmentName ? 'selected' : ''; ?>>
                                <?php echo riskEscape($departmentName); ?>
                            </option>
                        <?php } ?>
                    </select>
                </form>
            </div>

            <?php if (!$listed) { ?>
                <p class="text-muted text-center my-4">Nobody matches these filters.</p>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="risk-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Chance of resigning in 6 months</th>
                                <th>Band</th>
                                <th>Main reasons</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listed as $index => $row) {
                                $employee = $employees[$row['employee_id']] ?? null;
                                $width = $maxProbability > 0 ? round($row['risk_probability'] / $maxProbability * 100) : 0;
                            ?>
                                <tr>
                                    <td class="rank"><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php if ($employee) { ?>
                                            <a class="employee-name" href="./employee_form.php?id=<?php echo (int)$employee['id']; ?>">
                                                <?php echo riskEscape($employee['name']); ?>
                                            </a>
                                        <?php } else { ?>
                                            <span class="employee-name"><?php echo riskEscape($row['employee_id']); ?></span>
                                        <?php } ?>
                                        <div class="employee-meta"><?php echo riskEscape($row['department']); ?> &middot; <?php echo riskEscape($row['role']); ?></div>
                                    </td>
                                    <td>
                                        <div class="risk-meter" title="Bar is relative to the highest score">
                                            <div class="track"><div class="fill" style="width: <?php echo $width; ?>%"></div></div>
                                            <span class="value"><?php echo riskPercent($row['risk_probability']); ?></span>
                                        </div>
                                    </td>
                                    <td><span class="band-badge <?php echo riskEscape($row['risk_band']); ?>"><?php echo riskEscape($row['risk_band']); ?></span></td>
                                    <td>
                                        <?php foreach (['reason_1', 'reason_2', 'reason_3'] as $reasonKey) { ?>
                                            <?php if (!empty($row[$reasonKey])) { ?>
                                                <span class="reason-chip"><?php echo riskEscape($row[$reasonKey]); ?></span>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>

        <div class="row g-4">
            <!-- By department -->
            <div class="col-12 col-lg-6">
                <div class="risk-section h-100">
                    <h3><i class="bi bi-building-fill"></i> Risk by department</h3>
                    <p class="section-note">Average chance of resigning, and how many staff are in the High band.</p>
                    <?php foreach ($departmentSummary as $name => $summary) { ?>
                        <div class="department-row">
                            <span><?php echo riskEscape($name); ?></span>
                            <div class="track"><div class="fill" style="width: <?php echo round($summary['average'] / $maxDepartmentAverage * 100); ?>%"></div></div>
                            <span class="numbers"><?php echo riskPercent($summary['average']); ?> &middot; <?php echo $summary['high']; ?> of <?php echo $summary['staff']; ?> high</span>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Drivers -->
            <div class="col-12 col-lg-6">
                <div class="risk-section h-100">
                    <h3><i class="bi bi-sliders"></i> What drives risk</h3>
                    <p class="section-note">How much the odds of resigning change for each step above average in a signal.</p>
                    <div class="row g-4">
                        <div class="col-12 col-sm-6">
                            <p class="driver-heading">Raises risk</p>
                            <ul class="driver-list">
                                <?php foreach ($raising as $driver) { ?>
                                    <li><span><?php echo riskEscape($driver['label']); ?></span><span class="odds up">&times;<?php echo number_format(exp($driver['weight']), 2); ?></span></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <div class="col-12 col-sm-6">
                            <p class="driver-heading">Lowers risk</p>
                            <ul class="driver-list">
                                <?php foreach ($lowering as $driver) { ?>
                                    <li><span><?php echo riskEscape($driver['label']); ?></span><span class="odds down">&times;<?php echo number_format(exp($driver['weight']), 2); ?></span></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <?php if (!empty($model['training_positives'])) { ?>
                        <p class="section-note mt-3 mb-0">
                            Learned from <?php echo (int)$model['training_positives']; ?> resignations in <?php echo (int)$model['training_rows']; ?> quarterly records.
                        </p>
                    <?php } ?>
                </div>
            </div>
        </div>

        <p class="ethics-note">
            <i class="bi bi-shield-check"></i>
            These are estimates to help managers start supportive conversations early. They are not a judgement of anyone,
            and should never be used on their own for decisions about pay, promotion or dismissal.
        </p>
    <?php } ?>
</div>

<?php
    require_once 'admin_footer.php';
?>
