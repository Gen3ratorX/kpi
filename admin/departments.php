<?php
    require_once '../misc/admin_login_required.php';
    require_once '../misc/database_auth.php';
    $departmentCount = (int)$con->query("SELECT COUNT(*) FROM department")->fetch_row()[0];
    require_once '../controls/department_control.php';
    $departmentControl = new DepartmentControl($con);
    $departmentsHtml = $departmentControl->departmentAdminListTemplate();
?>

<?php
    $pageTitle = "NLA KPI Admin | Departments";
    require_once './admin_navbar.php';
?>
    <!-- Add item -->
    <section id="add-item">
        <a href="./department_form.php">+</a>
    </section><?php
    require_once '../misc/database_auth.php';
    require_once '../controls/department_control.php';
    $departmentControl = new DepartmentControl($con);
    $departmentsHtml = $departmentControl->departmentAdminListTemplate();
?>

<?php
    $pageTitle = "NLA KPI Admin | Departments";
    require_once './admin_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN DEPARTMENTS PAGE STYLES ===== */
    
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

    /* Add Item Button - Floating Action Button */
    #add-item {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 100;
    }

    #add-item a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        border-radius: 50%;
        font-size: 2.5rem;
        font-weight: 300;
        text-decoration: none;
        box-shadow: 0 8px 30px rgba(108, 99, 255, 0.4);
        transition: all 0.3s ease;
        border: none;
        outline: none;
    }

    #add-item a:hover {
        transform: scale(1.1) rotate(90deg);
        box-shadow: 0 12px 40px rgba(108, 99, 255, 0.6);
        background: linear-gradient(135deg, #7b73f5, #9a8cf9);
        color: white;
    }

    #add-item a i {
        font-size: 2rem;
    }

    /* Departments Section */
    .departments-section {
        background: white;
        border-radius: 24px;
        padding: 1.8rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .departments-section .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .departments-section .section-header h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .departments-section .section-header h3 i {
        color: #6c63ff;
    }

    .departments-section .section-header .department-count {
        background: #f0f2f5;
        padding: 0.3rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        color: #6c63ff;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .departments-section .section-header .department-count i {
        font-size: 0.9rem;
    }

    /* Departments Grid Wrapper */
    .departments-grid-wrapper {
        margin-top: 0.5rem;
    }

    .departments-grid-wrapper .department-item {
        background: white;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 16px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }

    .departments-grid-wrapper .department-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #6c63ff, #a78bfa);
        opacity: 0;
        transition: opacity 0.3s ease;
        border-radius: 0 2px 2px 0;
    }

    .departments-grid-wrapper .department-item:hover::before {
        opacity: 1;
    }

    .departments-grid-wrapper .department-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        border-color: rgba(108, 99, 255, 0.1);
    }

    .departments-grid-wrapper .department-item:last-child {
        margin-bottom: 0;
    }

    /* Department Card Content */
    .department-item .department-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .department-item .department-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a2e;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .department-item .department-name i {
        color: #6c63ff;
        font-size: 1.3rem;
        width: 24px;
        text-align: center;
    }

    .department-item .department-hod {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.9rem;
        background: #f8f9fa;
        padding: 0.3rem 1rem;
        border-radius: 50px;
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .department-item .department-hod i {
        color: #f59e0b;
        font-size: 0.9rem;
    }

    .department-item .department-hod strong {
        color: #1a1a2e;
    }

    .department-item .department-stats {
        display: flex;
        gap: 1.5rem;
        margin-top: 0.8rem;
        flex-wrap: wrap;
        padding-top: 0.8rem;
        border-top: 1px solid rgba(0, 0, 0, 0.03);
    }

    .department-item .department-stats span {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.85rem;
        color: #6b7280;
    }

    .department-item .department-stats span i {
        color: #6c63ff;
        font-size: 0.9rem;
    }

    .department-item .department-stats .stat-number {
        font-weight: 600;
        color: #1a1a2e;
    }

    .department-item .department-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0, 0, 0, 0.03);
        flex-wrap: wrap;
    }

    .department-item .department-actions .btn-action {
        padding: 0.3rem 1rem;
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
    }

    .btn-action.btn-view {
        background: #f0f2f5;
        color: #1a1a2e;
    }

    .btn-action.btn-view:hover {
        background: #e5e7eb;
        transform: translateY(-1px);
    }

    .btn-action.btn-edit {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-action.btn-edit:hover {
        background: #bfdbfe;
        transform: translateY(-1px);
    }

    .btn-action.btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-action.btn-delete:hover {
        background: #fecaca;
        transform: translateY(-1px);
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border-bottom: 1px solid rgba(239, 68, 68, 0.1);
        padding: 1.5rem;
    }

    .modal-header .modal-title {
        font-weight: 700;
        color: #991b1b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-header .modal-title i {
        font-size: 1.3rem;
    }

    .modal-body {
        padding: 2rem 1.5rem;
        text-align: center;
    }

    .modal-body .delete-icon {
        font-size: 4rem;
        color: #ef4444;
        margin-bottom: 1rem;
        display: block;
    }

    .modal-body h4 {
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 0.5rem;
    }

    .modal-body .delete-warning {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .btn-yes {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        border: none;
        padding: 0.6rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-yes:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
        color: white;
    }

    .btn-no {
        background: #f0f2f5;
        color: #1a1a2e;
        border: none;
        padding: 0.6rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-no:hover {
        background: #e5e7eb;
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

        #add-item a {
            width: 50px;
            height: 50px;
            font-size: 2rem;
        }

        .departments-section {
            padding: 1rem;
        }

        .departments-section .section-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .department-item .department-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .department-item .department-stats {
            gap: 0.8rem;
        }

        .department-item .department-actions {
            width: 100%;
        }

        .department-item .department-actions .btn-action {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .page-header .header-title h1 {
            font-size: 1.3rem;
        }

        .page-header .header-title h1 i {
            font-size: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem 1rem;
        }

        .department-item .department-name {
            font-size: 1rem;
        }

        .btn-yes, .btn-no {
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
        }
    }

    /* Department Color Variants - For visual variety */
    .department-item:nth-child(1)::before {
        background: linear-gradient(180deg, #6c63ff, #a78bfa);
    }
    .department-item:nth-child(2)::before {
        background: linear-gradient(180deg, #10b981, #34d399);
    }
    .department-item:nth-child(3)::before {
        background: linear-gradient(180deg, #f59e0b, #fbbf24);
    }
    .department-item:nth-child(4)::before {
        background: linear-gradient(180deg, #3b82f6, #60a5fa);
    }
    .department-item:nth-child(5)::before {
        background: linear-gradient(180deg, #ef4444, #f87171);
    }
    .department-item:nth-child(6)::before {
        background: linear-gradient(180deg, #8b5cf6, #a78bfa);
    }
</style>

<!-- ===== MAIN CONTENT ===== -->
<div class="dashboard-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-title">
            <h1>
                <i class="bi bi-building-fill"></i> Departments
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #6c63ff;"></i> 
                <?php echo date('l, F d, Y'); ?>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-building-fill"></i> 
                <?php echo $departmentCount; ?> Departments
            </span>
        </div>
    </div>

    <!-- Departments Section -->
    <div class="departments-section">
        <div class="section-header">
            <h3>
                <i class="bi bi-list-ul"></i> All Departments
            </h3>
            <span class="department-count">
                <i class="bi bi-building-fill"></i> 
                <?php echo $departmentCount; ?> Total
            </span>
        </div>
        
        <div class="departments-grid-wrapper">
            <?php echo $departmentsHtml; ?>
        </div>
    </div>
</div>

<!-- Add Item Button -->
<section id="add-item">
    <a href="./department_form.php" aria-label="Add new department">
        <i class="bi bi-plus"></i>
    </a>
</section>

<!-- Delete Item Modal -->
<div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteItemLabel">
                    <i class="bi bi-trash3-fill"></i> Delete Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span class="delete-icon">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </span>
                <h4 id="deleteItemBody">Are you sure you want to delete this department?</h4>
                <p class="delete-warning">
                    <i class="bi bi-info-circle-fill" style="color: #f59e0b;"></i>
                    This action cannot be undone. All associated data will be permanently removed.
                </p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-no" data-bs-dismiss="modal" id="close-delete-department">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button class="btn btn-yes" id="delete-department">
                        <i class="bi bi-check-circle"></i> Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    require_once './admin_footer.php';
?>
    <!-- Delete Item Modal -->
    <div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteItemLabel">Delete Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="deleteItemBody" class="text-secondary">
                        Are you sure you want to delete this department?
                    </h4>
                    <div class="text-end mt-4">
                        <button class="btn btn-2 btn-sm" id="delete-department">Yes</button>
                        <button class="btn btn-3 btn-sm" data-bs-dismiss="modal" id="close-delete-department">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Departments</h1>
                <?php echo $departmentsHtml; ?>
            </section>
        </div>
    </main>
<?php
    require_once './admin_footer.php';
?>