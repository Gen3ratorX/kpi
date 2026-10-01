<?php
    require_once '../misc/admin_login_required.php';
    require_once '../misc/database_auth.php';
    // Counts for the header badges: active staff, and everyone listed (including leavers)
    $activeEmployeeCount = (int)$con->query("SELECT COUNT(*) FROM employee WHERE status='active'")->fetch_row()[0];
    $employeeTotal = (int)$con->query("SELECT COUNT(*) FROM employee")->fetch_row()[0];
    require_once '../controls/employee_control.php';
    $pageNumber = max(1, (int)($_GET['page'] ?? 1));
    $employeeControl = new EmployeeControl($con);
    $employeesHtml = $employeeControl->employeeAdminListTemplate($page = $pageNumber);
    $uri = $_SERVER['REQUEST_URI'];
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
?>

<?php
    $pageTitle = "NLA KPI Admin | Employees";
    require_once 'admin_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN EMPLOYEE PAGE STYLES ===== */
    
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

    /* Employees Section */
    .employees-section {
        background: white;
        border-radius: 24px;
        padding: 1.8rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .employees-section .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .employees-section .section-header h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .employees-section .section-header h3 i {
        color: #6c63ff;
    }

    .employees-section .section-header .employee-count {
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

    .employees-section .section-header .employee-count i {
        font-size: 0.9rem;
    }

    /* Employees Grid Wrapper */
    .employees-grid-wrapper {
        margin-top: 0.5rem;
    }

    .employees-grid-wrapper .employee-item {
        background: white;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 16px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .employees-grid-wrapper .employee-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        border-color: rgba(108, 99, 255, 0.1);
    }

    .employees-grid-wrapper .employee-item:last-child {
        margin-bottom: 0;
    }

    /* Pagination Styling */
    .pagination-wrapper {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper .pagination {
        gap: 0.3rem;
    }

    .pagination-wrapper .page-link {
        border-radius: 10px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        padding: 0.5rem 1rem;
        color: #1a1a2e;
        font-weight: 500;
        transition: all 0.3s ease;
        background: white;
    }

    .pagination-wrapper .page-link:hover {
        background: rgba(108, 99, 255, 0.05);
        border-color: rgba(108, 99, 255, 0.2);
        color: #6c63ff;
        transform: translateY(-2px);
    }

    .pagination-wrapper .page-item.active .page-link {
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        border-color: #6c63ff;
        color: white;
        box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
    }

    .pagination-wrapper .page-item.disabled .page-link {
        color: #9ca3af;
        pointer-events: none;
        background: #f9fafb;
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

    .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1rem 1.5rem;
        justify-content: center;
        gap: 0.8rem;
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

    /* Employee Status Badge */
    .status-badge {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-badge.active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
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

        .employees-section {
            padding: 1rem;
        }

        .employees-section .section-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .pagination-wrapper .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-wrapper .page-link {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
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

        .btn-yes, .btn-no {
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
        }
    }
</style>

<!-- ===== MAIN CONTENT ===== -->
<div class="dashboard-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-title">
            <h1>
                <i class="bi bi-people-fill"></i> Employees
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #6c63ff;"></i> 
                <?php echo date('l, F d, Y'); ?>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-people-fill"></i> 
                <?php echo $activeEmployeeCount; ?> Employees
            </span>
        </div>
    </div>

    <!-- Employees Section -->
    <div class="employees-section">
        <div class="section-header">
            <h3>
                <i class="bi bi-list-ul"></i> All Employees
            </h3>
            <span class="employee-count">
                <i class="bi bi-people-fill"></i> 
                <?php echo $employeeTotal; ?> Total
            </span>
        </div>
        
        <div class="employees-grid-wrapper">
            <?php echo $employeesHtml; ?>
        </div>

        <!-- Pagination (if your EmployeeControl generates pagination) -->
        <?php if (isset($pageNumber)): ?>
        <div class="pagination-wrapper">
            <nav aria-label="Employee pagination">
                <ul class="pagination">
                    <li class="page-item <?php echo $pageNumber <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $url . '?page=' . ($pageNumber - 1); ?>" aria-label="Previous">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <li class="page-item <?php echo $pageNumber == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $url . '?page=' . $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $url . '?page=' . ($pageNumber + 1); ?>" aria-label="Next">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Item Button -->
<section id="add-item">
    <a href="./employee_form.php" aria-label="Add new employee">
        <i class="bi bi-plus"></i>
    </a>
</section>

<!-- Mark As Left Modal -->
<div class="modal fade" id="markLeft" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="markLeftLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markLeftLabel">
                    <i class="bi bi-box-arrow-right"></i> Mark Employee as Left
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="delete-warning">
                    <i class="bi bi-info-circle-fill" style="color: #f59e0b;"></i>
                    They will no longer be able to sign in. Their tasks and ratings are kept, and they can be reinstated later.
                </p>
                <div class="text-start">
                    <div class="mb-3">
                        <label for="exitDate" class="form-label">Exit date</label>
                        <input type="date" id="exitDate" class="form-control" max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="exitType" class="form-label">Reason for leaving</label>
                        <select id="exitType" class="form-control">
                            <option value="resigned">Resigned</option>
                            <option value="dismissed">Dismissed</option>
                            <option value="retired">Retired</option>
                            <option value="contract_ended">Contract ended</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exitReason" class="form-label">Notes (optional)</label>
                        <textarea id="exitReason" class="form-control" rows="3" placeholder="e.g. Moved to another organisation"></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-no" data-bs-dismiss="modal" id="close-mark-left">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button class="btn btn-yes" id="mark-left">
                        <i class="bi bi-check-circle"></i> Mark as Left
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    require_once 'admin_footer.php';
?>