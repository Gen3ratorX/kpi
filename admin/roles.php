<?php
    require_once '../misc/admin_login_required.php';
    require_once '../misc/database_auth.php';
    $roleCount = (int)$con->query("SELECT COUNT(*) FROM employee_role")->fetch_row()[0];
    require_once '../controls/role_control.php';

    $roleControl = new RoleControl($con);
    $rolesHtml = $roleControl->roleAdminListTemplate();
?>

<?php
    $pageTitle = "NLA KPI Admin | Roles";
    require_once './admin_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN ROLES PAGE STYLES ===== */
    
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

    /* Roles Section */
    .roles-section {
        background: white;
        border-radius: 24px;
        padding: 1.8rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .roles-section .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .roles-section .section-header h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .roles-section .section-header h3 i {
        color: #6c63ff;
    }

    .roles-section .section-header .role-count {
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

    .roles-section .section-header .role-count i {
        font-size: 0.9rem;
    }

    /* Roles Grid Wrapper */
    .roles-grid-wrapper {
        margin-top: 0.5rem;
    }

    .roles-grid-wrapper .role-item {
        background: white;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 16px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .roles-grid-wrapper .role-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        border-color: rgba(108, 99, 255, 0.1);
    }

    .roles-grid-wrapper .role-item:last-child {
        margin-bottom: 0;
    }

    /* Role Type Badge */
    .role-type-badge {
        display: inline-block;
        padding: 0.25rem 1rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .role-type-badge.admin {
        background: #dbeafe;
        color: #1e40af;
    }

    .role-type-badge.manager {
        background: #d1fae5;
        color: #065f46;
    }

    .role-type-badge.user {
        background: #fef3c7;
        color: #92400e;
    }

    .role-type-badge.guest {
        background: #f3f4f6;
        color: #4b5563;
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

    /* Role-specific styling for list items */
    .role-item .role-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a2e;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .role-item .role-name i {
        color: #6c63ff;
        font-size: 1.3rem;
    }

    .role-item .role-description {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 0.3rem;
    }

    .role-item .role-meta {
        display: flex;
        gap: 1.5rem;
        margin-top: 0.8rem;
        flex-wrap: wrap;
    }

    .role-item .role-meta span {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.85rem;
        color: #6b7280;
    }

    .role-item .role-meta span i {
        color: #6c63ff;
        font-size: 0.9rem;
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

        .roles-section {
            padding: 1rem;
        }

        .roles-section .section-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .role-item .role-meta {
            gap: 0.8rem;
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

        .role-item .role-name {
            font-size: 1rem;
            flex-wrap: wrap;
        }
    }
</style>

<!-- ===== MAIN CONTENT ===== -->
<div class="dashboard-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-title">
            <h1>
                <i class="bi bi-person-badge-fill"></i> Roles
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #6c63ff;"></i> 
                <?php echo date('l, F d, Y'); ?>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-person-badge-fill"></i> 
                <?php echo $roleCount; ?> Roles
            </span>
        </div>
    </div>

    <!-- Roles Section -->
    <div class="roles-section">
        <div class="section-header">
            <h3>
                <i class="bi bi-list-ul"></i> All Roles
            </h3>
            <span class="role-count">
                <i class="bi bi-person-badge-fill"></i> 
                <?php echo $roleCount; ?> Total
            </span>
        </div>
        
        <div class="roles-grid-wrapper">
            <?php echo $rolesHtml; ?>
        </div>
    </div>
</div>

<!-- Add Item Button -->
<section id="add-item">
    <a href="./role_form.php" aria-label="Add new role">
        <i class="bi bi-plus"></i>
    </a>
</section>

<!-- Delete Item Modal -->
<div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteItemLabel">
                    <i class="bi bi-trash3-fill"></i> Delete Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span class="delete-icon">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </span>
                <h4 id="deleteItemBody">Are you sure you want to delete this role?</h4>
                <p class="delete-warning">
                    <i class="bi bi-info-circle-fill" style="color: #f59e0b;"></i>
                    This cannot be undone. Roles that are still assigned to employees can't be deleted.
                </p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-no" data-bs-dismiss="modal" id="close-delete-role">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button class="btn btn-yes" id="delete-role">
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