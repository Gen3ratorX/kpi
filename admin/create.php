<?php
    require_once '../misc/admin_login_required.php';
    $pageTitle = "NLA KPI Admin | Create";
    require_once 'admin_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN CREATE PAGE STYLES ===== */
    
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

    /* Create Items Grid */
    .create-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 0.5rem;
    }

    .create-card {
        display: block;
        text-decoration: none;
        background: white;
        border-radius: 20px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        border: 1px solid rgba(0, 0, 0, 0.04);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .create-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #6c63ff, #a78bfa);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .create-card:hover::before {
        opacity: 1;
    }

    .create-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 50px rgba(0, 0, 0, 0.08);
        border-color: rgba(108, 99, 255, 0.1);
    }

    .create-card .card-icon {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.2rem;
        font-size: 2.5rem;
        transition: all 0.3s ease;
    }

    .create-card:hover .card-icon {
        transform: scale(1.05) rotate(-5deg);
    }

    .create-card .card-icon.purple {
        background: rgba(108, 99, 255, 0.1);
        color: #6c63ff;
    }

    .create-card .card-icon.blue {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .create-card .card-icon.green {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .create-card .card-icon.orange {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .create-card .card-icon.red {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .create-card .card-icon.teal {
        background: rgba(20, 184, 166, 0.1);
        color: #14b8a6;
    }

    .create-card .card-icon.pink {
        background: rgba(236, 72, 153, 0.1);
        color: #ec4899;
    }

    .create-card .card-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0 0 0.3rem 0;
        transition: color 0.3s ease;
    }

    .create-card:hover .card-title {
        color: #6c63ff;
    }

    .create-card .card-description {
        color: #6b7280;
        font-size: 0.9rem;
        margin: 0;
    }

    .create-card .card-arrow {
        display: inline-block;
        margin-top: 1rem;
        color: #6c63ff;
        font-size: 1.2rem;
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
    }

    .create-card:hover .card-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    /* Card color variants on hover */
    .create-card:nth-child(1):hover {
        border-color: rgba(108, 99, 255, 0.2);
    }
    .create-card:nth-child(2):hover {
        border-color: rgba(59, 130, 246, 0.2);
    }
    .create-card:nth-child(3):hover {
        border-color: rgba(16, 185, 129, 0.2);
    }
    .create-card:nth-child(4):hover {
        border-color: rgba(245, 158, 11, 0.2);
    }
    .create-card:nth-child(5):hover {
        border-color: rgba(239, 68, 68, 0.2);
    }

    /* Responsive Design */
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

        .create-grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .create-card {
            padding: 2rem 1rem;
        }

        .create-card .card-icon {
            width: 60px;
            height: 60px;
            font-size: 2rem;
        }

        .create-card .card-title {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 480px) {
        .page-header .header-title h1 {
            font-size: 1.3rem;
        }

        .page-header .header-title h1 i {
            font-size: 1.5rem;
        }

        .create-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
        }

        .create-card {
            padding: 1.5rem 0.8rem;
        }

        .create-card .card-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            border-radius: 14px;
        }

        .create-card .card-title {
            font-size: 0.95rem;
        }

        .create-card .card-description {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 380px) {
        .create-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- ===== MAIN CONTENT ===== -->
<div class="dashboard-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-title">
            <h1>
                <i class="bi bi-plus-circle-fill"></i> Create New
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #6c63ff;"></i> 
                <?php echo date('l, F d, Y'); ?>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-grid-fill"></i> 
                <?php 
                    $items = ['Project', 'Employee', 'Role', 'Department', 'Unit'];
                    echo count($items);
                ?> Options
            </span>
        </div>
    </div>

    <!-- Create Items Grid -->
    <div class="create-grid">
        <!-- Project -->
        <a href="project_form.php" class="create-card">
            <div class="card-icon purple">
                <i class="bi bi-folder-fill"></i>
            </div>
            <h3 class="card-title">Project</h3>
            <p class="card-description">Create a new project</p>
            <span class="card-arrow"><i class="bi bi-arrow-right-circle-fill"></i></span>
        </a>

        <!-- Employee -->
        <a href="./employee_form.php" class="create-card">
            <div class="card-icon blue">
                <i class="bi bi-person-fill"></i>
            </div>
            <h3 class="card-title">Employee</h3>
            <p class="card-description">Add a new employee</p>
            <span class="card-arrow"><i class="bi bi-arrow-right-circle-fill"></i></span>
        </a>

        <!-- Role -->
        <a href="role_form.php" class="create-card">
            <div class="card-icon green">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <h3 class="card-title">Role</h3>
            <p class="card-description">Create a new role</p>
            <span class="card-arrow"><i class="bi bi-arrow-right-circle-fill"></i></span>
        </a>

        <!-- Department -->
        <a href="./department_form.php" class="create-card">
            <div class="card-icon orange">
                <i class="bi bi-building-fill"></i>
            </div>
            <h3 class="card-title">Department</h3>
            <p class="card-description">Create a new department</p>
            <span class="card-arrow"><i class="bi bi-arrow-right-circle-fill"></i></span>
        </a>

        <!-- Unit -->
        <a href="./unit_form.php" class="create-card">
            <div class="card-icon red">
                <i class="bi bi-diagram-3-fill"></i>
            </div>
            <h3 class="card-title">Unit</h3>
            <p class="card-description">Create a new unit</p>
            <span class="card-arrow"><i class="bi bi-arrow-right-circle-fill"></i></span>
        </a>

        <!-- Additional Option - Task (if needed) -->
        <!-- 
        <a href="./task_form.php" class="create-card">
            <div class="card-icon teal">
                <i class="bi bi-check2-square"></i>
            </div>
            <h3 class="card-title">Task</h3>
            <p class="card-description">Create a new task</p>
            <span class="card-arrow"><i class="bi bi-arrow-right-circle-fill"></i></span>
        </a>
        -->

        <!-- Additional Option - Performance (if needed) -->
        <!-- 
        <a href="./performance_form.php" class="create-card">
            <div class="card-icon pink">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <h3 class="card-title">Performance</h3>
            <p class="card-description">Create a new performance record</p>
            <span class="card-arrow"><i class="bi bi-arrow-right-circle-fill"></i></span>
        </a>
        -->
    </div>
</div>

<?php
    require_once 'admin_footer.php';
?>