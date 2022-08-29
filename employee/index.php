
<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    session_start();
    $projectControl = new ProjectControl($con);
    $employeeId = $_SESSION['employeeId'];
    $employeeRole = $_SESSION['employeeRole'];
    $projectsHtml = $projectControl->generateEmployeeProjectList($employeeId,$employeeRole)
?>

<?php
    $pageTitle = "NLA KPI | Projects";
    require_once 'employee_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Projects</h1>
                <section>
                    <?php echo $projectsHtml;?>
                </section>
            </section>
        </div>
    </main>
<?php
    require_once 'employee_footer.php';
?>