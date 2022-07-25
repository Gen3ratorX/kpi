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