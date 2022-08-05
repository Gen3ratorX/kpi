
<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    $projectControl = new ProjectControl($con);
?>

<?php
    $projectId = $_GET['id'];
    if($projectId){
        // Get project
        $project = $projectControl->getProject($projectId);
        if($project){
            $employeeId = 1;
            $projectName = $project['name'];
            // Days left
            $currentDate = new DateTime();
            $deadline = new DateTime($project['deadline']);
            $daysLeft = $deadline->diff($currentDate)->format('%a');
            // Tasks
            $tasks = $projectControl->employeeTasks($employeeId,$projectId);
            // Progress
            $progress = $projectControl->employeeProgress($employeeId,$projectId);
        }
        else{
            header("Location: ./");
        }
    }
    else{
        header("Location: ./");
    }
?>

<?php
    $pageTitle = "NLA KPI | Project -  $projectName";
    require_once 'employee_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header"><?php echo $projectName;?></h1>
                <!-- General Items -->
                <section class="row my-3 row-cols-1 g-3 row-cols-md-3">
                    <!-- Progress -->
                    <div class="col">
                        <div class="card shadow-sm project-detail-general-dashboard-item h-100" style="background-color: #56c186;">
                            <div class="card-body">
                                <div class="d-flex align-items-center jusify-content-around">
                                    <section class="order-2">
                                        <img src="../static/images/progress.png" width="35px" alt="Progress">
                                    </section>
                                    <section class="flex-grow-1 me-1">
                                        <h4>Total Progress</h4>
                                        <p><?php echo $progress;?>%</p>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tasks -->
                    <div class="col">
                        <div class="card shadow-sm project-detail-general-dashboard-item h-100" style="background-color: #4cc1ef;">
                            <div class="card-body">
                                <div class="d-flex align-items-center jusify-content-around">
                                    <section class="order-2">
                                        <img src="../static/images/tasks.png" width="35px" alt="Tasks">
                                    </section>
                                    <section class="flex-grow-1 me-1">
                                        <h4>Tasks</h4>
                                        <p><?php echo $tasks; ?></p>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Days Left -->
                    <div class="col">
                        <div class="card shadow-sm project-detail-general-dashboard-item h-100" style="background-color: #ef7f85;">
                            <div class="card-body">
                                <div class="d-flex align-items-center jusify-content-around">
                                    <section class="order-2">
                                        <img src="../static/images/days_left.png" width="35px" alt="Days Left">
                                    </section>
                                    <section class="flex-grow-1 me-1">
                                        <h4>Days Left</h4>
                                        <p><?php echo $daysLeft; ?></p>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </section>
        </div>
    </main>
<?php
    require_once 'employee_footer.php';
?>