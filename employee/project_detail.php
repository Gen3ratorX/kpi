<?php
require_once '../misc/employee_login_required.php';
require_once '../misc/database_auth.php';
require_once '../controls/project_control.php';
require_once '../controls/task_control.php';
$projectControl = new ProjectControl($con);
$taskControl = new TaskControl($con);
?>

<?php
$projectId = $_GET['id'];
if ($projectId) {
    // Get project
    $project = $projectControl->getProject($projectId);
    if ($project) {
        $employeeId = $_SESSION['employeeId'];
        $employeeRole = $_SESSION['employeeRole'];
        $projectName = $project['name'];
        $assessProject = $project['assess'];
        $projectIsOpen = $project['is_open'];
        // Days left
        $currentDate = new DateTime();
        $deadline = new DateTime($project['deadline']);
        $daysLeft = $deadline->diff($currentDate)->format('%a');
        // Check if employee is assigned
        if ($taskControl->isEmployeeAssignedToProject($employeeRole, $employeeId, $projectId)) {
            // TODO: Special roles
            // Auditor or General Manager
            if ($employeeRole == 1 or $employeeRole == 0) {
                $generalItems = $projectControl->generalDashboardItems($projectId);
                $tasks = $generalItems['tasks'];
                $progress = $generalItems['totalProgress'];
            }
            // Manager or staff
            else {
                $tasks = $projectControl->employeeTasks($employeeId, $projectId);
                $progress = $projectControl->employeeProgress($employeeId, $projectId);
                $tasksHtml = $taskControl->generateEmployeeTasksHtml($employeeId, $projectId);
            }
        } else {
            header("Location: ./");
            exit;
        }
    } else {
        header("Location: ./");
        exit;
    }
} else {
    header("Location: ./");
    exit;
}
?>

<?php
$pageTitle = "KPI | Project -  $projectName";
require_once 'employee_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN EMPLOYEE PROJECT DETAIL PAGE STYLES ===== */
    
    /* Main container */
    .dashboard-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
        padding-bottom: 1.2rem;
        border-bottom: 2px solid rgba(16, 185, 129, 0.15);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header .header-title h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }

    .page-header .header-title h1 i {
        color: #10b981;
        font-size: 2rem;
    }

    .page-header .header-title .subtitle {
        color: #6b7280;
        margin: 0.3rem 0 0 0;
        font-size: 0.9rem;
    }

    .page-header .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .header-badge {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
        padding: 0.5rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-badge i {
        font-size: 1rem;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        border-radius: 16px;
        padding: 1.5rem;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
        border: none;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .stat-card:hover::before {
        transform: scale(1.5);
        opacity: 0.5;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .stat-card .stat-icon {
        float: right;
        font-size: 2rem;
        opacity: 0.3;
    }

    .stat-card .stat-label {
        font-size: 0.85rem;
        font-weight: 500;
        opacity: 0.9;
        margin: 0;
    }

    .stat-card .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0.3rem 0 0 0;
        line-height: 1;
    }

    .stat-card .stat-sub {
        font-size: 0.8rem;
        opacity: 0.8;
        margin: 0.3rem 0 0 0;
    }

    .stat-card.green {
        background: linear-gradient(135deg, #10b981, #34d399);
    }

    .stat-card.blue {
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
    }

    .stat-card.orange {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
    }

    .stat-card.red {
        background: linear-gradient(135deg, #ef4444, #f87171);
    }

    /* Status Messages */
    .status-message {
        background: white;
        border-radius: 24px;
        padding: 3rem 2rem;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .status-message i {
        font-size: 4rem;
        color: #f59e0b;
        display: block;
        margin-bottom: 1rem;
    }

    .status-message h4 {
        color: #1a1a2e;
        margin-bottom: 0.5rem;
    }

    .status-message p {
        color: #6b7280;
        margin: 0;
    }

    /* Floating Action Button - Back to Projects */
    .fab-back {
        position: fixed;
        bottom: 2rem;
        left: 2rem;
        z-index: 100;
    }

    .fab-back a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        color: white;
        border-radius: 50%;
        font-size: 1.8rem;
        text-decoration: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        border: none;
        outline: none;
    }

    .fab-back a:hover {
        transform: scale(1.1) rotate(-5deg);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        background: linear-gradient(135deg, #10b981, #34d399);
    }

    .fab-back a i {
        font-size: 1.8rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-header .header-title h1 {
            font-size: 1.5rem;
        }

        .page-header .header-actions {
            width: 100%;
        }

        .header-badge {
            width: 100%;
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .fab-back {
            bottom: 1rem;
            left: 1rem;
        }

        .fab-back a {
            width: 48px;
            height: 48px;
            font-size: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .page-header .header-title h1 {
            font-size: 1.3rem;
        }

        .page-header .header-title h1 i {
            font-size: 1.5rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .stat-card .stat-value {
            font-size: 2rem;
        }

        .status-message {
            padding: 2rem 1rem;
        }

        .status-message i {
            font-size: 3rem;
        }
    }
</style>

<!-- ===== MAIN CONTENT ===== -->
<div class="dashboard-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-title">
            <h1>
                <i class="bi bi-folder-fill"></i> 
                <?php echo $projectName; ?>
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #10b981;"></i> 
                <?php echo date('l, F d, Y'); ?>
                <span style="color: #6b7280; margin-left: 1rem;">
                    <i class="bi bi-tag-fill" style="color: #10b981;"></i> 
                    Project ID: #<?php echo $projectId; ?>
                </span>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-info-circle-fill"></i> 
                <?php echo $projectIsOpen ? '🟢 Open' : '🔴 Closed'; ?>
                <?php echo $assessProject ? ' | 📝 Assessment' : ''; ?>
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <!-- Progress -->
        <div class="stat-card green">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <p class="stat-label">Total Progress</p>
            <p class="stat-value"><?php echo $progress; ?>%</p>
            <p class="stat-sub">overall completion</p>
        </div>

        <!-- Tasks -->
        <div class="stat-card blue">
            <div class="stat-icon"><i class="bi bi-list-check"></i></div>
            <p class="stat-label">Tasks</p>
            <p class="stat-value" id="no-of-tasks"><?php echo $tasks; ?></p>
            <p class="stat-sub">total assigned</p>
        </div>

        <!-- Days Left -->
        <div class="stat-card orange">
            <div class="stat-icon"><i class="bi bi-calendar-count"></i></div>
            <p class="stat-label">Days Left</p>
            <p class="stat-value"><?php echo $daysLeft; ?></p>
            <p class="stat-sub">until deadline</p>
        </div>
    </div>

    <!-- Content Section -->
    <section class="mt-4">
        <?php
        if ($employeeRole != 1  and !$assessProject and $projectIsOpen) {
            require_once './staff_and_manager_tasks.php';
        } elseif ($employeeRole != 1  and !$assessProject and !$projectIsOpen) {
            echo '
                <div class="status-message">
                    <i class="bi bi-lock-fill"></i>
                    <h4>Project is Closed</h4>
                    <p>The project isn\'t open for adding tasks. Please contact your administrator.</p>
                </div>
            ';
        }
        // Assessment
        else {
            require_once './assessment.php';
        }
        ?>
    </section>
</div>

<!-- Floating Action Button - Back to Projects -->
<div class="fab-back">
    <a href="./index.php" title="Back to Projects">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>

<?php
require_once 'employee_footer.php';
?>