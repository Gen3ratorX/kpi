<?php
require_once '../misc/admin_login_required.php';
require_once '../misc/database_auth.php';
require_once '../controls/department_control.php';
$departmentControl = new DepartmentControl($con);
?>

<?php
$departmentId = $_GET['id'] ?? '';
$departmentName = "";
$hod = "";
// // Check if we are editing
if ($departmentId) {
    $department = $departmentControl->getDepartment($departmentId);
    if ($department) {
        $departmentName = $department['name'];
        $hod = $department['hod'] ?? '';
    }
}
?>

<?php
$pageTitle = "NLA KPI Admin | Create Department";
require_once 'admin_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN CREATE DEPARTMENT PAGE STYLES ===== */
    
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

    .page-header .header-title .subtitle .edit-badge {
        display: inline-block;
        background: #dbeafe;
        color: #1e40af;
        padding: 0.2rem 1rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 500;
        margin-left: 0.5rem;
    }

    .page-header .header-title .subtitle .edit-badge i {
        font-size: 0.8rem;
        color: #1e40af;
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

    /* Floating Action Button - Back to Departments */
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
        color: #1a1a2e;
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

    .form-group .form-hint {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 0.3rem;
    }

    .form-group .form-hint i {
        color: #6c63ff;
        font-size: 0.8rem;
    }

    /* Department Icon Preview */
    .department-icon-preview {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: #f9fafb;
        padding: 0.8rem 1.2rem;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        margin-bottom: 1rem;
    }

    .department-icon-preview i {
        font-size: 2rem;
        color: #6c63ff;
        background: rgba(108, 99, 255, 0.1);
        padding: 0.5rem;
        border-radius: 10px;
    }

    .department-icon-preview .preview-text {
        font-size: 0.9rem;
        color: #6b7280;
    }

    .department-icon-preview .preview-text strong {
        color: #1a1a2e;
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

        .department-icon-preview {
            flex-direction: column;
            text-align: center;
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

        .form-group .form-control {
            padding: 0.6rem 0.8rem;
            font-size: 0.9rem;
        }

        .btn-save {
            padding: 0.8rem 1.5rem;
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
                <i class="bi bi-building-plus"></i> 
                <?php echo $departmentName ? 'Edit Department' : 'Create Department'; ?>
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #6c63ff;"></i> 
                <?php echo date('l, F d, Y'); ?>
                <?php if($departmentName): ?>
                    <span class="edit-badge">
                        <i class="bi bi-pencil-fill"></i> Editing: <?php echo htmlspecialchars($departmentName); ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="header-actions">
            <span class="header-badge">
                <i class="bi bi-info-circle-fill"></i> 
                All fields marked with * are required
            </span>
        </div>
    </div>

    <!-- Department Form -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-building-fill"></i> Department Information
        </div>
        <p class="section-subtitle">Enter the department details below.</p>

        <!-- Department Icon Preview -->
        <div class="department-icon-preview">
            <i class="bi bi-building"></i>
            <div class="preview-text">
                <?php if($departmentName): ?>
                    <strong><?php echo htmlspecialchars($departmentName); ?></strong> 
                    <span style="color: #6b7280;">department</span>
                <?php else: ?>
                    <span style="color: #6b7280;">Department preview will appear here</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Department Name -->
        <div class="form-group">
            <label for="department" class="required">Department Name</label>
            <input type="text" value="<?php echo $departmentName; ?>" class="form-control" id="department" placeholder="Enter department name...">
            <div class="invalid-feedback">
                <i class="bi bi-exclamation-circle-fill"></i> Please provide a department name.
            </div>
            <div class="form-hint">
                <i class="bi bi-info-circle-fill"></i> Enter the full name of the department (e.g., "Information Technology", "Human Resources").
            </div>
        </div>

        <!-- Head of Department -->
        <div class="form-group">
            <label for="hod" class="required">Head of Department</label>
            <input type="text" value="<?php echo $hod; ?>" class="form-control" id="hod" placeholder="Enter head of department name...">
            <div class="invalid-feedback">
                <i class="bi bi-exclamation-circle-fill"></i> Please provide a head of department name.
            </div>
            <div class="form-hint">
                <i class="bi bi-info-circle-fill"></i> Enter the full name of the department head (e.g., "Dr. John Smith").
            </div>
        </div>

        <!-- Info Card -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-info" style="background: #f0f4ff; border: 1px solid #dbeafe; border-radius: 12px; color: #1e40af; padding: 0.8rem 1.2rem;">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <?php if($departmentName): ?>
                        You are editing the <strong><?php echo htmlspecialchars($departmentName); ?></strong> department.
                        <?php if($hod): ?>
                            Head of Department: <strong><?php echo htmlspecialchars($hod); ?></strong>
                        <?php endif; ?>
                    <?php else: ?>
                        Create a new department. Each department can have multiple units and employees assigned to it.
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="save-btn-wrapper">
            <button type="button" id="save-department" data-department-edit="<?php echo $departmentName ? 'true' : 'false'; ?>" data-department-id="<?php echo $departmentName ? $departmentId : ''; ?>" class="btn-save">
                <i class="bi <?php echo $departmentName ? 'bi-pencil-fill' : 'bi-check-circle-fill'; ?>"></i>
                <?php echo $departmentName ? 'Update Department' : 'Save Department'; ?>
            </button>
        </div>
    </div>
</div>

<!-- Floating Action Button - Back to Departments -->
<div class="fab-back">
    <a href="./departments.php" title="Back to Departments">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>

<!-- ===== JAVASCRIPT FOR VALIDATION ===== -->
<script>
$(document).ready(function() {
    // Live preview of department name
    $('#department').on('input', function() {
        const value = $(this).val().trim();
        if (value) {
            $('.department-icon-preview .preview-text').html('<strong>' + value + '</strong> <span style="color: #6b7280;">department</span>');
        } else {
            $('.department-icon-preview .preview-text').html('<span style="color: #6b7280;">Department preview will appear here</span>');
        }
    });

    // Form validation for save button
    $('#save-department').on('click', function() {
        let isValid = true;
        const $department = $('#department');
        const $hod = $('#hod');
        
        // Validate department name
        const deptValue = $department.val().trim();
        if (!deptValue) {
            $department.addClass('is-invalid');
            $department.removeClass('is-valid');
            isValid = false;
        } else {
            $department.removeClass('is-invalid');
            $department.addClass('is-valid');
        }

        // Validate HOD
        const hodValue = $hod.val().trim();
        if (!hodValue) {
            $hod.addClass('is-invalid');
            $hod.removeClass('is-valid');
            isValid = false;
        } else {
            $hod.removeClass('is-invalid');
            $hod.addClass('is-valid');
        }

        if (!isValid) {
            baseControl.showToast('Please fill in all required fields correctly.');
        }
    });

    // Real-time validation on blur
    $('#department, #hod').on('blur', function() {
        const $field = $(this);
        const value = $field.val().trim();
        const isRequired = $field.closest('.form-group').find('.required').length > 0;
        
        if (isRequired) {
            if (!value) {
                $field.addClass('is-invalid');
                $field.removeClass('is-valid');
            } else {
                $field.removeClass('is-invalid');
                $field.addClass('is-valid');
            }
        }
    });

    // Remove invalid class on input
    $('#department, #hod').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>

<?php
require_once 'admin_footer.php';
?>