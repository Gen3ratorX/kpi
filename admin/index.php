
<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    $projectControl = new ProjectControl($con);
    $projectsHtml = $projectControl->projectAdminListTemplate();
?>

<?php
    $pageTitle = "NLA KPI Admin | Home";
    require_once 'admin_navbar.php';
?>

    <!-- Add item -->
    <section id="add-item">
        <a href="./project_form.php">+</a>
    </section>

    <!-- Delete Item Modal -->
    <div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteItemLabel">Delete Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="deleteItemBody" class="text-secondary">
                        Are you sure you want to delete this project?
                    </h4>
                    <div class="text-end mt-4">
                        <button class="btn btn-2 btn-sm" id="delete-project">Yes</button>
                        <button class="btn btn-3 btn-sm" data-bs-dismiss="modal" id="close-delete-project">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Projects</h1>
                <?php echo $projectsHtml; ?>
                <!-- <div id="projects">
                    <div class='card shadow-sm action-item'>
                        <div class='options d-flex align-items-center justify-content-center'>
                            <div>
                                <button class='btn btn-md btn-2 me-2 delete-employee-attempt' id='' data-bs-toggle='modal' data-bs-target='#deleteItem'>Delete</button>
                                <a href='' class='btn btn-md btn-4 me-2'>Edit</a>
                                <a href='' class='btn btn-md btn-3'>View</a>
                            </div>
                        </div>
                        <div class='card-body d-flex justify-content-between align-items-center'>
                            <div class="flex-grow-1">
                                <h4 class="project-name">Lorem ipsum dolor sit amet consectetur a</h4>
                                <div class="row g-2 row-cols-1 row-cols-sm-2 mt-3">
                                    <p class="project-item col">Tasks: <span>30</span></p>
                                    <p class="project-item col">Employees Assigned: <span>30</span></p>
                                </div>
                            </div>
                            <h1 class='text-success display-4'>100%</h1>
                        </div>
                    </div>
                </div> -->
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>