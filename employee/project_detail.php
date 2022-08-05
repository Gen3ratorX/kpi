
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
            $employeeRole = 2;
            $projectName = $project['name'];
            // Days left
            $currentDate = new DateTime();
            $deadline = new DateTime($project['deadline']);
            $daysLeft = $deadline->diff($currentDate)->format('%a');
            if($employeeRole == 1){
                $generalItems = $projectControl->generalDashboardItems($projectId);
                $tasks = $generalItems['tasks'];
                $progress = $generalItems['totalProgress'];
            }
            else{
                $tasks = $projectControl->employeeTasks($employeeId,$projectId);
                $progress = $projectControl->employeeProgress($employeeId,$projectId);
            }
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
    <!-- Add item -->
    <section id="add-item">
        <p data-bs-toggle="modal" data-bs-target="#addItem">+</p>
    </section>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addItemLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemLabel">Delete Employee</h5>
                    <button type="button" class="btn-close" id="close-add-item" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="task" class="required">Task</label>
                    <textarea id="task" class="form-control resizable"></textarea>
                    <div class="invalid-feedback">
                        Please provide a task.
                    </div>
                    <div class="text-center my-5">
                        <button class="btn btn-1 btn-md" id="add-task">Add Task</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                <section>
                    <!-- Tasks -->
                    <h3 class="text-secondary">Tasks</h3>
                    <section>
                        <div class="no-item">
                            No task has been added...
                            <div class="text-center">
                                <button data-bs-toggle="modal" data-bs-target="#addItem" type="button" href="" class="btn btn-md btn-1">Add Task</button>
                            </div>
                        </div>
                    </section>
                </section>
            </section>
        </div>
    </main>
<?php
    require_once 'employee_footer.php';
?>