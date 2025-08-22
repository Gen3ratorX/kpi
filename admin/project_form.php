<?php
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
<main id='main-body'
    <div class="container">
    <section class="mb-3">
        <h1 class="header">Create Project</h1>
        <section class="row g-3">
            <div class="col-12 col-sm-8">
                <label for="projectName" class="required">Project Name:</label>
                <input type="text" id="projectName" value="" class="form-control">
                <div class="invalid-feedback">
                    Please provide a project name.
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <label for="projectTarget" class="required">Target:</label>
                <input class="form-control" id="projectTarget" type="number" min='0' max='100'>
                <div class="invalid-feedback">
                    Please provide a valid target eg 20,30,40,50 etc.
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <label for="projectDeadline" class="required">Deadline:</label>
                <input type="date" min="<?php echo $tomorrowsDate; ?>" id="projectDeadline" value="" class="form-control">
                <div class="invalid-feedback">
                    Please provide a deadline.
                </div>
            </div>
            <div class="col-12 col-sm-6 align-self-center" id="isOpen" data-value="0">
                <label>
                    Is Open:
                </label> <br>
                <button class="btn btn-4-outline btn-sm">Yes</button>
                <button class="btn btn-4-solid btn-sm">No</button>
            </div>
            <div class="col-12">
                <label for="projectEmployees">Employees Assigned:</label>
                <section class="my-2" id="assigned-employees">
                    <p class='text-center text-muted lead'>No Employee Has Been Assigned.</p>
                </section>
            </div>
        </section>

        <!-- Assign Employees -->
        <h1 class="header">Assign Employees</h1>
        <?php echo $assignEmployeesHtml; ?>
        <!-- Save -->
        <div class="text-center my-5">
            <button type="button" id="save-project" class="btn btn-1 btn-lg">Save Project</button>
        </div>
        </div>
</main>
<?php
require_once 'admin_footer.php';
?>