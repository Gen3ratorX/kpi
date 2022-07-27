<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/employee_control.php';
    $employeeControl = new EmployeeControl($con);
    $employeesHtml = $employeeControl->employeeAdminListTemplate();
?>

<?php
    $pageTitle = "NLA KPI Admin | Employees";
    require_once 'admin_navbar.php';
?>
    <!-- Delete Item Modal -->
    <div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteItemLabel">Delete Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="deleteItemBody" class="text-secondary">
                        Are you sure you want to delete this employee?
                    </h4>
                    <div class="text-end mt-4">
                        <button class="btn btn-2 btn-sm" id="delete-employee">Yes</button>
                        <button class="btn btn-3 btn-sm" data-bs-dismiss="modal" id="close-delete-employee">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main id='main-body'
        <div class="container-md">
            <section class="mb-3">
                <h1 class="header">Employees</h1>
                    <?php echo $employeesHtml;?>
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>