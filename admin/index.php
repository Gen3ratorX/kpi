
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
    <style>
    </style>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Projects</h1>
                <?php echo $projectsHtml; ?>
            </section>
            <section class="row row-cols-4">
                <div class="col below-average" style="height: 100px; ">
                </div>
                <div class="col average" style="height: 100px; ">
                </div>
                <div class="col above-average" style="height: 100px; ">
                </div>
                <div class="col excellent" style="height: 100px; ">
                </div>
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>