
<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    require_once '../controls/project_control.php';
    $projectControl = new ProjectControl($con);
    $projectsHtml = $projectControl->generateEmployeeProjectList(1);
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
                    <!-- <a href="#">
                        <div class='card shadow-sm item mb-3'>
                            <div class='card-body d-flex justify-content-between align-items-center'>
                                <div class='flex-grow-1'>
                                    <h4 class='project-name'>Project Name</h4>
                                    <div class='d-flex flex-column flex-md-row'>
                                        <p class='project-item'>Tasks: <span>34</span></p>
                                        <p class='project-item'>Days Left: <span>45</span></p>
                                    </div>
                                </div>
                                <h1 class='text-success display-4'>64%</h1>
                            </div>
                        </div>
                    </a> -->
                    <?php echo $projectsHtml;?>
                </section>
            </section>
        </div>
    </main>
<?php
    require_once 'employee_footer.php';
?>