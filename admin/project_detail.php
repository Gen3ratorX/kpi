<?php
    require_once '../misc/admin_login_required.php';
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    $projectControl = new ProjectControl($con);
?>

<?php
    $projectId = $_GET['id'];
    if($projectId){
        // Get project
        $project = $projectControl->getProject($projectId);
        if($project){
            $isOpen = $project['is_open'];
            $assess = $project['assess'];
            $idealTarget = $project['target'];
            $generalItems = $projectControl->generalDashboardItems($projectId);
            $employeeProgressHtml = $projectControl->generateEmployeeProgressItems($projectId);
            $departmentProgressHtml = $projectControl->generateDepartmentProgressItems($projectId);
        }
        else{
            header("Location:./");
            exit;
        }
    }
    else{
        header("Location: ./");
        exit;
    }
?>

<?php
    $pageTitle = "NLA KPI Admin | Project - {$generalItems['projectName']}";
    require_once 'admin_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN PROJECT DETAILS PAGE STYLES ===== */
    
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
        border-bottom: 2px solid rgba(108, 99, 255, 0.08);
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
        color: #6c63ff;
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
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        padding: 0.5rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
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

    /* Options Section */
    .options-wrapper {
        background: white;
        border-radius: 24px;
        padding: 1.8rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .options-wrapper:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
    }

    .options-wrapper .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .options-wrapper .section-title i {
        color: #6c63ff;
    }

    .options-wrapper .section-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin: -0.3rem 0 1.5rem 0;
    }

    .option-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.2rem 1.5rem;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        height: 100%;
    }

    .option-card:hover {
        border-color: #6c63ff;
        background: #f5f3ff;
    }

    .option-card label {
        font-weight: 600;
        color: #1a1a2e;
        font-size: 0.9rem;
        display: block;
        margin-bottom: 0.5rem;
    }

    .option-card label i {
        color: #6c63ff;
        margin-right: 0.3rem;
    }

    /* Progress Sections */
    .progress-wrapper {
        background: white;
        border-radius: 24px;
        padding: 1.8rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .progress-wrapper:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
    }

    .progress-wrapper .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .progress-wrapper .section-title i {
        color: #6c63ff;
    }

    .progress-wrapper .section-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin: -0.3rem 0 1.5rem 0;
    }

    /* Progress Items - styling for items generated by PHP */
    .progress-item {
        background: white;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 16px;
        padding: 1.2rem 1.5rem;
        margin-bottom: 0.8rem;
        transition: all 0.3s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .progress-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        border-color: rgba(108, 99, 255, 0.1);
    }

    .progress-item:last-child {
        margin-bottom: 0;
    }

    .progress-item .details {
        flex: 1;
        min-width: 200px;
    }

    .progress-item .details .name {
        font-weight: 600;
        color: #1a1a2e;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .progress-item .details .name i {
        color: #6c63ff;
    }

    .progress-item .details .meta {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        margin-top: 0.3rem;
    }

    .progress-item .details .meta span {
        font-size: 0.8rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .progress-item .details .meta span i {
        color: #6c63ff;
        font-size: 0.8rem;
    }

    .progress-item .percentage {
        font-size: 1.8rem;
        font-weight: 700;
        min-width: 80px;
        text-align: right;
        padding: 0.3rem 0.8rem;
        border-radius: 12px;
        background: #f8fafc;
    }

    .progress-item .percentage.high {
        color: #10b981;
        background: #f0fdf4;
    }

    .progress-item .percentage.medium {
        color: #f59e0b;
        background: #fffbeb;
    }

    .progress-item .percentage.low {
        color: #ef4444;
        background: #fef2f2;
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
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
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

        .option-card {
            padding: 1rem;
        }

        .progress-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .progress-item .percentage {
            text-align: left;
            width: 100%;
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

        .progress-item .details .meta {
            gap: 0.8rem;
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
                <?php echo $generalItems['projectName']; ?>
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #6c63ff;"></i> 
                <?php echo date('l, F d, Y'); ?>
                <span style="color: #6b7280; margin-left: 1rem;">
                    <i class="bi bi-tag-fill" style="color: #6c63ff;"></i> 
                    Project ID: #<?php echo $projectId; ?>
                </span>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-info-circle-fill"></i> 
                <?php echo $isOpen ? '🟢 Open' : '🔴 Closed'; ?>
                <?php echo $assess ? ' | 📝 Assessing' : ''; ?>
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <!-- Achieved Target -->
        <div class="stat-card green">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <p class="stat-label">Achieved Target</p>
            <p class="stat-value"><?php echo $generalItems['totalProgress']; ?>%</p>
            <p class="stat-sub">of target achieved</p>
        </div>

        <!-- Ideal Target -->
        <div class="stat-card blue">
            <div class="stat-icon"><i class="bi bi-bullseye"></i></div>
            <p class="stat-label">Ideal Target</p>
            <p class="stat-value"><?php echo $idealTarget; ?>%</p>
            <p class="stat-sub">project goal</p>
        </div>

        <!-- Tasks -->
        <div class="stat-card orange">
            <div class="stat-icon"><i class="bi bi-list-check"></i></div>
            <p class="stat-label">Tasks</p>
            <p class="stat-value"><?php echo $generalItems['tasks']; ?></p>
            <p class="stat-sub">total tasks created</p>
        </div>

        <!-- Employees Assigned -->
        <div class="stat-card red">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <p class="stat-label">Employees Assigned</p>
            <p class="stat-value"><?php echo $generalItems['employeesAssigned']; ?></p>
            <p class="stat-sub">team members</p>
        </div>
    </div>

    <!-- Project Options -->
    <div class="options-wrapper">
        <div class="section-title">
            <i class="bi bi-gear-fill"></i> Project Options
        </div>
        <p class="section-subtitle">Configure project settings and permissions.</p>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="option-card">
                    <label><i class="bi bi-check-circle"></i> Assess Employees:</label>
                    <div>
                        <?php
                            echo $assess
                                ? "
                                    <button class='btn btn-1-solid assess-employees-toggle' data-value='yes'>Yes</button>
                                    <button class='btn btn-2-outline assess-employees-toggle' data-value='no'>No</button>
                                "
                                : "
                                    <button class='btn btn-1-outline assess-employees-toggle' data-value='yes'>Yes</button>
                                    <button class='btn btn-2-solid assess-employees-toggle' data-value='no'>No</button>
                                ";
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="option-card">
                    <label><i class="bi bi-plus-circle"></i> Employees Can Add Tasks:</label>
                    <div>
                        <?php
                            echo $isOpen
                            ? "
                                <button class='btn btn-1-solid employees-can-add-tasks-toggle' data-value='yes'>Yes</button>
                                <button class='btn btn-2-outline employees-can-add-tasks-toggle' data-value='no'>No</button>
                            "
                            : "
                                <button class='btn btn-1-outline employees-can-add-tasks-toggle' data-value='yes'>Yes</button>
                                <button class='btn btn-2-solid employees-can-add-tasks-toggle' data-value='no'>No</button>
                            ";
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Departments Progress -->
    <div class="progress-wrapper">
        <div class="section-title">
            <i class="bi bi-building-fill"></i> Department Progress
        </div>
        <p class="section-subtitle">Performance overview by department.</p>
        <?php echo $departmentProgressHtml; ?>
    </div>

    <!-- Employee Progress -->
    <div class="progress-wrapper">
        <div class="section-title">
            <i class="bi bi-people-fill"></i> Employee Progress
        </div>
        <p class="section-subtitle">Individual performance tracking.</p>
        <?php echo $employeeProgressHtml; ?>
    </div>
</div>

<!-- Floating Action Button - Back to Projects -->
<div class="fab-back">
    <a href="./index.php" title="Back to Projects">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>

<!-- ===== JAVASCRIPT FOR TOGGLE BUTTONS ===== -->
<script>
$(document).ready(function() {
    // ===== ASSESS EMPLOYEES TOGGLE =====
    $('.assess-employees-toggle').on('click', function() {
        const $btn = $(this);
        const value = $btn.data('value');
        const $group = $btn.closest('div');
        const projectId = <?php echo $projectId; ?>;
        
        // Update button styles
        $group.find('.assess-employees-toggle').removeClass('btn-1-solid btn-2-solid btn-1-outline btn-2-outline');
        
        if (value === 'yes') {
            $group.find('.assess-employees-toggle[data-value="yes"]')
                .addClass('btn-1-solid')
                .removeClass('btn-1-outline');
            $group.find('.assess-employees-toggle[data-value="no"]')
                .addClass('btn-2-outline')
                .removeClass('btn-2-solid');
        } else {
            $group.find('.assess-employees-toggle[data-value="yes"]')
                .addClass('btn-1-outline')
                .removeClass('btn-1-solid');
            $group.find('.assess-employees-toggle[data-value="no"]')
                .addClass('btn-2-solid')
                .removeClass('btn-2-outline');
        }
        
        // Show loading state
        $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>');
        $btn.prop('disabled', true);
        
        // AJAX call to update assess status
        $.ajax({
            url: '../controls/project_control.php',
            type: 'POST',
            data: {
                task: 'updateAssess',
                project_id: projectId,
                value: value === 'yes' ? 1 : 0
            },
            dataType: 'json',
            success: function(response) {
                // Reset buttons
                $group.find('.assess-employees-toggle').html(function() {
                    return $(this).data('value') === 'yes' ? 'Yes' : 'No';
                });
                $group.find('.assess-employees-toggle').prop('disabled', false);
                
                if (response.status === 'SUCCESS') {
                    baseControl.showToast('Assessment status updated successfully!');
                    updateAssessBadge(value === 'yes');
                } else {
                    baseControl.showToast(response.message || 'Failed to update assessment status.');
                }
            },
            error: function(xhr, status, error) {
                // Reset buttons
                $group.find('.assess-employees-toggle').html(function() {
                    return $(this).data('value') === 'yes' ? 'Yes' : 'No';
                });
                $group.find('.assess-employees-toggle').prop('disabled', false);
                
                console.error('AJAX Error:', xhr, status, error);
                console.error('Response Text:', xhr.responseText);
                baseControl.showToast('Error updating assessment status. Please try again.');
            }
        });
    });

    // ===== EMPLOYEES CAN ADD TASKS TOGGLE =====
    $('.employees-can-add-tasks-toggle').on('click', function() {
        const $btn = $(this);
        const value = $btn.data('value');
        const $group = $btn.closest('div');
        const projectId = <?php echo $projectId; ?>;
        
        // Update button styles
        $group.find('.employees-can-add-tasks-toggle').removeClass('btn-1-solid btn-2-solid btn-1-outline btn-2-outline');
        
        if (value === 'yes') {
            $group.find('.employees-can-add-tasks-toggle[data-value="yes"]')
                .addClass('btn-1-solid')
                .removeClass('btn-1-outline');
            $group.find('.employees-can-add-tasks-toggle[data-value="no"]')
                .addClass('btn-2-outline')
                .removeClass('btn-2-solid');
        } else {
            $group.find('.employees-can-add-tasks-toggle[data-value="yes"]')
                .addClass('btn-1-outline')
                .removeClass('btn-1-solid');
            $group.find('.employees-can-add-tasks-toggle[data-value="no"]')
                .addClass('btn-2-solid')
                .removeClass('btn-2-outline');
        }
        
        // Show loading state
        $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>');
        $btn.prop('disabled', true);
        
        // AJAX call to update is_open status
        $.ajax({
            url: '../controls/project_control.php',
            type: 'POST',
            data: {
                task: 'updateIsOpen',
                project_id: projectId,
                value: value === 'yes' ? 1 : 0
            },
            dataType: 'json',
            success: function(response) {
                // Reset buttons
                $group.find('.employees-can-add-tasks-toggle').html(function() {
                    return $(this).data('value') === 'yes' ? 'Yes' : 'No';
                });
                $group.find('.employees-can-add-tasks-toggle').prop('disabled', false);
                
                if (response.status === 'SUCCESS') {
                    baseControl.showToast('Project status updated successfully!');
                    updateStatusBadge(value === 'yes');
                } else {
                    baseControl.showToast(response.message || 'Failed to update project status.');
                }
            },
            error: function(xhr, status, error) {
                // Reset buttons
                $group.find('.employees-can-add-tasks-toggle').html(function() {
                    return $(this).data('value') === 'yes' ? 'Yes' : 'No';
                });
                $group.find('.employees-can-add-tasks-toggle').prop('disabled', false);
                
                console.error('AJAX Error:', xhr, status, error);
                console.error('Response Text:', xhr.responseText);
                baseControl.showToast('Error updating project status. Please try again.');
            }
        });
    });

    // ===== UPDATE STATUS BADGE =====
    function updateStatusBadge(isOpen) {
        const $badge = $('.header-badge');
        const statusText = isOpen ? '🟢 Open' : '🔴 Closed';
        const assessText = <?php echo json_encode($assess); ?> ? ' | 📝 Assessing' : '';
        $badge.html(`<i class="bi bi-info-circle-fill"></i> ${statusText}${assessText}`);
    }

    // ===== UPDATE ASSESS BADGE =====
    function updateAssessBadge(isAssessing) {
        const $badge = $('.header-badge');
        const isOpenText = <?php echo json_encode($isOpen); ?> ? '🟢 Open' : '🔴 Closed';
        const assessText = isAssessing ? ' | 📝 Assessing' : '';
        $badge.html(`<i class="bi bi-info-circle-fill"></i> ${isOpenText}${assessText}`);
    }
});
</script>

<?php
    require_once 'admin_footer.php';
?>