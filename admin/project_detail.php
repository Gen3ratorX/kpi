
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
            $generalItems = $projectControl->generalDashboardItems($projectId);
            $employeeProgressHtml = $projectControl->generateEmployeeProgressItems($projectId);
            $departmentProgressHtml = $projectControl->generateDepartmentProgressItems($projectId);
        }
        else{
            header("Location:./");
        }
    }
    else{
        header("Location: ./");
    }
?>

<?php
    $pageTitle = "NLA KPI Admin | Project - {$generalItems['projectName']}";
    require_once 'admin_navbar.php';
?>

    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header"><?php echo $generalItems['projectName'];?></h1>
                <!-- General Items -->
                <section class="row my-3 row-cols-1 g-3 row-cols-md-2 row-cols-lg-4">
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
                                        <p><?php echo $generalItems['totalProgress'];?>%</p>
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
                                        <p><?php echo $generalItems['tasks']; ?></p>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Employees Assigned -->
                    <div class="col">
                        <div class="card shadow-sm project-detail-general-dashboard-item h-100" style="background-color: #f4a03e;">
                            <div class="card-body">
                                <div class="d-flex align-items-center jusify-content-around">
                                    <section class="order-2">
                                        <img src="../static/images/employees_assigned.png" width="35px" alt="Employees Assigned">
                                    </section>
                                    <section class="flex-grow-1 me-1">
                                        <h4>Employees Assigned</h4>
                                        <p><?php echo $generalItems['employeesAssigned'];?></p>
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
                                        <p><?php echo $generalItems['daysLeft']; ?></p>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Departments Progress -->
                <section class="mt-5">
                    <h3 style="font-weight: 400;" class="text-secondary">Department Progress</h3>
                    <!-- <div class='card item shadow-sm department-progress-item mb-3'>
                        <div class='card-body d-flex justify-content-between align-items-center'>
                            <section class='details text-start'>
                                God the Father
                                <div class='d-flex flex-sm-row flex-column'>
                                    <p class='m-0 text-start small text-secondary'>Tasks: 25</p>
                                    <p class='m-0 text-start small text-secondary ms-sm-3'>Employees Assigned: 25</p>
                                </div>
                            </section>
                            <section class='percentage text-danger'>
                                100%
                            </section>
                        </div>
                    </div> -->
                    <?php echo $departmentProgressHtml; ?>
                </section>

                <!-- Employee Progress -->
                <section class="mt-5">
                    <h3 style="font-weight: 400;" class="text-secondary">Employee Progress</h3>
                    <!-- <div class='card item shadow-sm department-progress-item mb-3'>
                        <div class='card-body d-flex justify-content-between align-items-center'>
                            <section class='details text-start'>
                                Jesus Christ
                                <div class='d-flex flex-sm-row flex-column'>
                                    <p class='m-0 text-start small text-secondary'>Tasks: 25</p>
                                    <p class='m-0 text-start small text-secondary ms-sm-3'>Department: Human Resource</p>
                                </div>
                            </section>
                            <section class='percentage text-danger'>
                                100%
                            </section>
                        </div>
                    </div> -->
                    <?php echo $employeeProgressHtml; ?>
                </section>
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>