<?php
    require_once '../misc/admin_login_required.php';
    require_once '../misc/database_auth.php';
    require_once '../controls/role_control.php';
    require_once '../controls/employee_control.php';
    require_once '../controls/department_control.php';
    require_once '../controls/unit_control.php';
    $roleControl = new RoleControl($con);
    $roles = $roleControl->getRolesList();
    $departmentControl = new DepartmentControl($con);
    $departments = $departmentControl->getDepartmentsList();
    $unitControl = new UnitControl($con);
    $employeeControl = new EmployeeControl($con);
    $rolesValues = $employeeControl->generateRolesValues($roles);
    $departmentsValues = $employeeControl->generateDepartmentsValues($departments);
    $unitsValues = $employeeControl->generateUnitsValues()
?>

<?php
    $employeeId = $_GET['id'] ?? '';
    $surname = "";
    $otherNames = "";
    $phone = "";
    $email = "";
    $location = "";
    $username = "";
    $hireDate = "";
    if($employeeId){
        $employee = $employeeControl->getEmployee($employeeId);
        if($employee){
            $surname = $employee['surname'];
            $otherNames = $employee['other_names'];
            $phone = $employee['phone'];
            $email = $employee['email'];
            $username = $employee['username'];
            $location = $employee['location'];
            $hireDate = $employee['hire_date'] ?? '';
            $roleId = $employee['employee_role_id'];
            $departmentId = $employee['department_id'] ?: null;
            $unitId = $employee['unit_id'] ?: null;
            $rolesValues = $employeeControl->generateRolesValues($roles,$roleId);
            $departmentsValues = $employeeControl->generateDepartmentsValues($departments,$departmentId);
            $unitsValues = $departmentId ? $employeeControl->generateUnitsValues($departmentId,$unitId) : '';
        }
    }
?>

<?php
    $pageTitle = "NLA KPI Admin | Create Employee";
    require_once 'admin_navbar.php';
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN CREATE EMPLOYEE PAGE STYLES ===== */
    
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

    .form-group .form-control option {
        padding: 0.5rem;
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

    /* Select styling */
    .form-group select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
        cursor: pointer;
    }

    .form-group select.form-control:focus {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236c63ff' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
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
                <i class="bi bi-person-plus-fill"></i> 
                <?php echo $surname ? 'Edit Employee' : 'Create Employee'; ?>
            </h1>
            <p class="subtitle">
                <i class="bi bi-clock-fill" style="color: #6c63ff;"></i> 
                <?php echo date('l, F d, Y'); ?>
                <?php if($surname): ?>
                    <span class="badge bg-info ms-2" style="background: #dbeafe !important; color: #1e40af;">
                        <i class="bi bi-pencil-fill"></i> Editing: <?php echo htmlspecialchars($surname . ' ' . $otherNames); ?>
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

    <!-- Employee Form -->
    <div class="form-section">
        <div class="section-title">
            <i class="bi bi-person-fill"></i> Employee Information
        </div>
        <p class="section-subtitle">Fill in the employee details below.</p>

        <div class="row g-3">
            <!-- Surname -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="surname" class="required">Surname</label>
                    <input type="text" value="<?php echo $surname; ?>" class="form-control" id="surname" placeholder="Enter surname...">
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide a surname.
                    </div>
                </div>
            </div>

            <!-- Other Names -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="otherNames" class="required">Other Names</label>
                    <input type="text" id="otherNames" value="<?php echo $otherNames; ?>" class="form-control" placeholder="Enter other names...">
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide other names.
                    </div>
                </div>
            </div>

            <!-- Phone -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="phone" class="required">Phone</label>
                    <input type="text" value="<?php echo $phone; ?>" id="phone" class="form-control" placeholder="Enter phone number...">
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide a phone number.
                    </div>
                </div>
            </div>

            <!-- Email -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="email" class="required">Email</label>
                    <input type="email" id="email" value="<?php echo $email; ?>" class="form-control" placeholder="Enter email address...">
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Enter a valid email or leave it blank.
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="location" class="required">Location</label>
                    <input type="text" value="<?php echo $location; ?>" id="location" class="form-control" placeholder="Enter location...">
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide a location.
                    </div>
                </div>
            </div>

            <!-- Username -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="username" class="required">Username</label>
                    <input type="text" class="form-control" value="<?php echo $username; ?>" id="username" placeholder="Enter username...">
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide a username.
                    </div>
                </div>
            </div>

            <!-- Role -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="role" class="required">Role</label>
                    <select id="role" class="form-control">
                        <?php echo $rolesValues ?>
                    </select>
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide a role.
                    </div>
                </div>
            </div>

            <!-- Department -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="department" class="required">Department</label>
                    <select id="department" class="form-control">
                        <?php echo $departmentsValues ?>
                    </select>
                </div>
            </div>

            <!-- Unit -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="unit">Unit</label>
                    <select id="unit" class="form-control">
                        <option value="">Select Unit</option>
                        <?php echo $unitsValues ?>
                    </select>
                </div>
            </div>

            <!-- Hire Date -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="hireDate">Hire Date</label>
                    <input type="date" id="hireDate" class="form-control" value="<?php echo $hireDate; ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="save-btn-wrapper">
            <button type="button" class="btn-save" id="save-employee" data-employee-edit="<?php echo $surname ? 'true' : 'false'; ?>" data-employee-id="<?php echo $surname ? $employeeId : ''; ?>">
                <i class="bi <?php echo $surname ? 'bi-pencil-fill' : 'bi-check-circle-fill'; ?>"></i>
                <?php echo $surname ? 'Update Employee' : 'Save Employee'; ?>
            </button>
        </div>
    </div>
</div>

<!-- Floating Action Button - Back to Dashboard -->
<div class="fab-back">
    <a href="./employees.php" title="Back to Employees">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>

<!-- ===== JAVASCRIPT FOR DYNAMIC UNITS ===== -->
<script>
$(document).ready(function() {
    // Department change - load units dynamically
    const $department = $('#department');
    const $unit = $('#unit');

    $department.on('change', function() {
        const departmentId = $(this).val();
        
        if (departmentId) {
            $.ajax({
                url: '../controls/ajax_handlers.php',
                type: 'POST',
                data: {
                    task: 'getUnits',
                    department_id: departmentId
                },
                dataType: 'json',
                success: function(response) {
                    $unit.html('<option value="">Select Unit</option>');
                    if (response && response.length > 0) {
                        $.each(response, function(index, unit) {
                            const selected = <?php echo json_encode($unitId ?? null); ?> == unit.id ? 'selected' : '';
                            $unit.append(`<option value="${unit.id}" ${selected}>${unit.name}</option>`);
                        });
                    } else {
                        $unit.append('<option value="">No units available</option>');
                    }
                },
                error: function() {
                    console.log('Error loading units');
                }
            });
        } else {
            $unit.html('<option value="">Select Unit</option>');
        }
    });

    // Form validation for save button
    $('#save-employee').on('click', function() {
        let isValid = true;
        const requiredFields = ['#surname', '#otherNames', '#phone', '#email', '#location', '#username', '#role'];
        
        requiredFields.forEach(function(field) {
            const $field = $(field);
            const value = $field.val().trim();
            if (!value) {
                $field.addClass('is-invalid');
                $field.removeClass('is-valid');
                isValid = false;
            } else {
                $field.removeClass('is-invalid');
                $field.addClass('is-valid');
            }
        });

        // Email validation
        const email = $('#email').val().trim();
        if (email) {
            const emailRegex = /^[a-zA-Z][a-zA-Z0-9]+@[a-z]+\.[a-z]{2,}(\.[a-z]{2,})?$/;
            if (!emailRegex.test(email)) {
                $('#email').addClass('is-invalid');
                $('#email').removeClass('is-valid');
                isValid = false;
            }
        }

        if (!isValid) {
            // Show toast message
            baseControl.showToast('Please fill in all required fields correctly.');
        }
    });

    // Real-time validation on blur
    $('.form-control').on('blur', function() {
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
    $('.form-control').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>

<?php
    require_once 'admin_footer.php';
?>