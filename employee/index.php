<?php
    require_once '../misc/employee_login_required.php';
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    $projectControl = new ProjectControl($con);
    $employeeId = $_SESSION['employeeId'];
    $employeeRole = $_SESSION['employeeRole'];
    $projectsHtml = $projectControl->generateEmployeeProjectList($employeeId,$employeeRole)
?>

<?php
    $pageTitle = "NLA KPI | Projects";
    require_once 'employee_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN EMPLOYEE PROJECTS PAGE STYLES ===== */
    
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

    /* Projects Section */
    .projects-wrapper {
        background: white;
        border-radius: 24px;
        padding: 1.8rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
    }

    .projects-wrapper:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
    }

    .projects-wrapper .section-title {
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

    .projects-wrapper .section-title i {
        color: #10b981;
    }

    .projects-wrapper .section-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin: -0.3rem 0 1.5rem 0;
    }

    /* Project Cards */
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 0.5rem;
    }

    .project-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }

    .project-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981, #34d399);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .project-card:hover::before {
        opacity: 1;
    }

    .project-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        border-color: rgba(16, 185, 129, 0.15);
    }

    .project-card .project-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.8rem;
    }

    .project-card .project-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .project-card .project-name i {
        color: #10b981;
        font-size: 1.2rem;
    }

    .project-card .project-status {
        padding: 0.2rem 0.8rem;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .project-status.open {
        background: #d1fae5;
        color: #065f46;
    }

    .project-status.closed {
        background: #fee2e2;
        color: #991b1b;
    }

    .project-status.assess {
        background: #dbeafe;
        color: #1e40af;
    }

    .project-card .project-details {
        margin: 0.8rem 0;
        display: grid;
        gap: 0.4rem;
    }

    .project-card .project-details p {
        margin: 0;
        font-size: 0.85rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .project-card .project-details p i {
        color: #10b981;
        width: 18px;
        font-size: 0.9rem;
    }

    .project-card .project-progress {
        margin: 1rem 0;
    }

    .project-card .progress-bar-wrapper {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .project-card .progress-track {
        flex: 1;
        height: 6px;
        background: #f0f2f5;
        border-radius: 4px;
        overflow: hidden;
    }

    .project-card .progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.6s ease;
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .project-card .progress-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1a1a2e;
        min-width: 45px;
        text-align: right;
    }

    .project-card .project-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .project-card .project-actions .btn-action {
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        flex: 1;
        justify-content: center;
    }

    .btn-action.btn-view {
        background: #f0f2f5;
        color: #1a1a2e;
    }

    .btn-action.btn-view:hover {
        background: #e5e7eb;
    }

    .btn-action.btn-progress {
        background: #d1fae5;
        color: #065f46;
    }

    .btn-action.btn-progress:hover {
        background: #a7f3d0;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #d1d5db;
    }

    .empty-state h4 {
        color: #6b7280;
        margin-bottom: 0.5rem;
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

        .projects-wrapper {
            padding: 1rem;
        }

        .projects-grid {
            grid-template-columns: 1fr;
        }

        .project-card .project-actions {
            flex-wrap: wrap;
        }

        .project-card .project-actions .btn-action {
            flex: 1;
            min-width: 100px;
        }
    }

    @media (max-width: 480px) {
        .page-header .header-title h1 {
            font-size: 1.3rem;
        }

        .page-header .header-title h1 i {
            font-size: 1.5rem;
        }

        .project-card {
            padding: 1.2rem;
        }

        .project-card .project-name {
            font-size: 1rem;
        }
    }
</style>

<!-- ===== MAIN CONTENT ===== -->
<div class="dashboard-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-title">
            <h1>
                <i class="bi bi-folder-fill"></i> My Projects
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #10b981;"></i> 
                <?php echo date('l, F d, Y'); ?>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-folder-fill"></i> 
                <?php 
                    // Count projects from the HTML
                    preg_match_all('/<div class="project-card"/', $projectsHtml, $matches);
                    $projectCount = isset($matches[0]) ? count($matches[0]) : 0;
                    echo $projectCount;
                ?> Projects
            </span>
        </div>
    </div>

    <!-- Projects Section -->
    <div class="projects-wrapper">
        <div class="section-title">
            <i class="bi bi-list-ul"></i> All Projects
        </div>
        <p class="section-subtitle">View and track your assigned projects.</p>

        <?php 
        // Display projects using the template
        if (!empty($projectsHtml) && trim($projectsHtml) !== '') {
            echo '<div class="projects-grid">';
            echo $projectsHtml;
            echo '</div>';
        } else { 
        ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="bi bi-folder-plus"></i>
                <h4>No Projects Assigned</h4>
                <p>You haven't been assigned to any projects yet.</p>
                <p style="font-size: 0.85rem; color: #9ca3af;">
                    <i class="bi bi-info-circle-fill"></i> Contact your administrator for project assignments.
                </p>
            </div>
        <?php } ?>
    </div>
</div>

<?php
    require_once 'employee_footer.php';
?>