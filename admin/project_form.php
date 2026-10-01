<?php
require_once '../misc/admin_login_required.php';
require_once '../misc/database_auth.php';
require_once '../controls/employee_control.php';
require_once '../controls/project_control.php';

$pageNumber = 1;
$employeeControl = new EmployeeControl($con);
$employees = $employeeControl->getEmployeesList($pageNumber, '', []);
$projectControl = new ProjectControl($con);
$assignEmployeesHtml = $projectControl->generateAssignEmployeesHtml();


?>

<?php
$tomorrowsDate = date('Y-m-d', strtotime('tomorrow'));
?>

<?php
$pageTitle = "NLA KPI Admin | Create Project";
require_once 'admin_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN CREATE PROJECT PAGE STYLES ===== */
    
    /* Main container */
    .dashboard-wrapper {
        max-width: 1200px;
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

    /* Floating Action Button - Back to Dashboard */
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

    /* Form Section */
    .form-section {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .form-section:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
    }

    .form-section .section-title {
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

    .form-section .section-title i {
        color: #6c63ff;
    }

    .form-section .section-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin: -0.3rem 0 1.5rem 0;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 0.4rem;
        font-size: 0.9rem;
    }

    .form-group label.required::after {
        content: ' *';
        color: #ef4444;
        font-weight: 700;
    }

    .form-group .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.7rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f9fafb;
        width: 100%;
    }

    .form-group .form-control:focus {
        border-color: #6c63ff;
        background: white;
        box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.1);
        outline: none;
    }

    .form-group .form-control.is-invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .form-group .form-control.is-valid {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .form-group .invalid-feedback {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.3rem;
        display: none;
    }

    .form-group .invalid-feedback.show {
        display: block;
    }

    /* Toggle Buttons */
    .toggle-group {
        display: flex;
        gap: 0.5rem;
        padding-top: 0.3rem;
        flex-wrap: wrap;
    }

    .toggle-btn {
        padding: 0.5rem 1.8rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 2px solid #e5e7eb;
        background: transparent;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .toggle-btn:hover {
        border-color: #6c63ff;
        color: #6c63ff;
        transform: translateY(-2px);
    }

    .toggle-btn.active {
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        border-color: #6c63ff;
        box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
    }

    .toggle-btn.active:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108, 99, 255, 0.4);
    }

    /* Assigned Employees Section */
    .assigned-employees {
        background: #f9fafb;
        border-radius: 16px;
        padding: 1.5rem;
        min-height: 80px;
        border: 2px dashed #e5e7eb;
        transition: all 0.3s ease;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.3rem;
    }

    .assigned-employees.has-employees {
        border-color: #6c63ff;
        background: #f5f3ff;
        border-style: solid;
    }

    .assigned-employees .empty-message {
        color: #9ca3af;
        text-align: center;
        margin: 0;
        font-size: 1rem;
        width: 100%;
    }

    .assigned-employees .empty-message i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        color: #d1d5db;
    }

    .assigned-employee-item {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        padding: 0.3rem 0.8rem 0.3rem 0.8rem;
        border-radius: 50px;
        margin: 0.2rem;
        border: 1px solid rgba(108, 99, 255, 0.15);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        font-size: 0.85rem;
        color: #1a1a2e;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .assigned-employee-item i {
        color: #6c63ff;
        font-size: 0.9rem;
    }

    .assigned-employee-item .remove-employee {
        cursor: pointer;
        color: #ef4444;
        margin-left: 0.2rem;
        transition: all 0.3s ease;
        background: none;
        border: none;
        padding: 0 0.2rem;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
    }

    .assigned-employee-item .remove-employee:hover {
        transform: scale(1.3);
        color: #dc2626;
    }

    /* Assign Employees Grid */
    .assign-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }

    .assign-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 14px;
        padding: 1rem 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .assign-card:hover {
        border-color: #6c63ff;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }

    .assign-card.selected {
        border-color: #6c63ff;
        background: #f5f3ff;
        box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.08);
    }

    .assign-card .assign-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6c63ff, #a78bfa);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .assign-card .assign-info {
        flex: 1;
        min-width: 0;
    }

    .assign-card .assign-info .assign-name {
        font-weight: 600;
        color: #1a1a2e;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .assign-card .assign-info .assign-detail {
        color: #6b7280;
        font-size: 0.75rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .assign-card .assign-check {
        color: #6c63ff;
        font-size: 1.4rem;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .assign-card.selected .assign-check {
        opacity: 1;
    }

    /* Save Button */
    .save-btn-wrapper {
        text-align: center;
        margin: 2.5rem 0 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .btn-save {
        background: linear-gradient(135deg, #6c63ff, #8b7cf7);
        color: white;
        border: none;
        padding: 0.9rem 4rem;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(108, 99, 255, 0.3);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
    }

    .btn-save:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 35px rgba(108, 99, 255, 0.5);
        background: linear-gradient(135deg, #7b73f5, #9a8cf9);
    }

    .btn-save:active {
        transform: scale(0.97);
    }

    .btn-save i {
        font-size: 1.3rem;
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

        .form-section {
            padding: 1.2rem;
        }

        .assign-grid {
            grid-template-columns: 1fr;
        }

        .toggle-group {
            flex-wrap: wrap;
        }

        .btn-save {
            width: 100%;
            justify-content: center;
            padding: 0.9rem 2rem;
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

        .form-section {
            padding: 1rem;
        }

        .assigned-employees {
            padding: 1rem;
            min-height: 60px;
        }

        .assign-card {
            padding: 0.8rem;
        }

        .toggle-btn {
            padding: 0.4rem 1.2rem;
            font-size: 0.8rem;
        }
    }
</style>

<!-- ===== MAIN CONTENT ===== -->
<div class="dashboard-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-title">
            <h1>
                <i class="bi bi-folder-plus"></i> Create Project
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #6c63ff;"></i> 
                <?php echo date('l, F d, Y'); ?>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-info-circle-fill"></i> 
                All fields marked with * are required
            </span>
        </div>
    </div>

    <!-- Project Details Form -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-info-circle-fill"></i> Project Details
        </div>
        <p class="section-subtitle">Fill in the basic information for the new project.</p>

        <div class="row g-3">
            <!-- Project Name -->
            <div class="col-12 col-md-8">
                <div class="form-group">
                    <label for="projectName" class="required">Project Name</label>
                    <input type="text" id="projectName" value="" class="form-control" placeholder="Enter project name...">
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide a project name.
                    </div>
                </div>
            </div>

            <!-- Target -->
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="projectTarget" class="required">Target (%)</label>
                    <input class="form-control" id="projectTarget" type="number" min="0" max="100" placeholder="e.g. 75">
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide a valid target (0-100).
                    </div>
                </div>
            </div>

            <!-- Deadline -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="projectDeadline" class="required">Deadline</label>
                    <input type="date" min="<?php echo $tomorrowsDate; ?>" id="projectDeadline" value="" class="form-control">
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide a valid deadline.
                    </div>
                </div>
            </div>

            <!-- Is Open Toggle -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Project Status</label>
                    <div id="isOpen" data-value="0" class="toggle-group">
                        <button class="toggle-btn active" data-value="1">
                            <i class="bi bi-check-circle-fill"></i> Open
                        </button>
                        <button class="toggle-btn" data-value="0">
                            <i class="bi bi-x-circle-fill"></i> Closed
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Employees -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-people-fill"></i> Assigned Employees
        </div>
        <p class="section-subtitle">Employees assigned to this project will be listed below.</p>

        <div class="assigned-employees" id="assigned-employees">
            <p class="empty-message">
                <i class="bi bi-person-plus"></i>
                No employees have been assigned yet.
            </p>
        </div>
    </div>

    <!-- Assign Employees -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-person-plus-fill"></i> Assign Employees
        </div>
        <p class="section-subtitle">Click on an employee to assign them to this project.</p>

        <div class="assign-grid">
            <?php echo $assignEmployeesHtml; ?>
        </div>

        <!-- Save Button -->
        <div class="save-btn-wrapper">
            <button type="button" id="save-project" class="btn-save">
                <i class="bi bi-check-circle-fill"></i>
                Save Project
            </button>
        </div>
    </div>
</div>

<!-- Floating Action Button - Back to Dashboard -->
<div class="fab-back">
    <a href="./index.php" title="Back to Dashboard">
        <i class="bi bi-house-fill"></i>
    </a>
</div>

<!-- ===== JAVASCRIPT FOR TOGGLE FUNCTIONALITY ===== -->
<script>
$(document).ready(function() {
    // Toggle buttons functionality
    const $toggleGroup = $('#isOpen');
    const $toggleBtns = $toggleGroup.find('.toggle-btn');

    $toggleBtns.on('click', function() {
        const $btn = $(this);
        const value = $btn.data('value');
        
        // Update active state
        $toggleBtns.removeClass('active');
        $btn.addClass('active');
        
        // Update data attribute
        $toggleGroup.data('value', value);
        
        console.log('Project status set to:', value === 1 ? 'Open' : 'Closed');
    });

    // Employee assignment functionality
    const $assignCards = $('.assign-card');
    const $assignedContainer = $('#assigned-employees');

    $assignCards.on('click', function() {
        const $card = $(this);
        $card.toggleClass('selected');
        
        const employeeName = $card.find('.assign-name').text();
        const employeeId = $card.data('employee-id') || $card.find('.assign-name').data('id') || Date.now();
        
        if ($card.hasClass('selected')) {
            addAssignedEmployee(employeeName, employeeId);
        } else {
            removeAssignedEmployee(employeeId);
        }
    });

    function addAssignedEmployee(name, id) {
        // Remove empty message if exists
        $assignedContainer.find('.empty-message').remove();
        $assignedContainer.addClass('has-employees');
        
        // Check if already added
        if ($assignedContainer.find(`[data-employee-id="${id}"]`).length > 0) {
            return;
        }
        
        // Create employee item
        const $item = $(`
            <span class="assigned-employee-item" data-employee-id="${id}">
                <i class="bi bi-person-fill"></i>
                ${name}
                <button class="remove-employee" title="Remove employee">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </span>
        `);
        
        // Add remove functionality
        $item.find('.remove-employee').on('click', function(e) {
            e.stopPropagation();
            const $parent = $(this).closest('.assigned-employee-item');
            const empId = $parent.data('employee-id');
            removeAssignedEmployee(empId);
            // Also unselect the card
            $(`.assign-card[data-employee-id="${empId}"]`).removeClass('selected');
        });
        
        $assignedContainer.append($item);
    }

    function removeAssignedEmployee(id) {
        $(`.assigned-employee-item[data-employee-id="${id}"]`).remove();
        
        // Show empty message if no employees left
        if ($assignedContainer.children('.assigned-employee-item').length === 0) {
            $assignedContainer.removeClass('has-employees');
            $assignedContainer.html(`
                <p class="empty-message">
                    <i class="bi bi-person-plus"></i>
                    No employees have been assigned yet.
                </p>
            `);
        }
    }
});
</script>

<?php
require_once 'admin_footer.php';
?>