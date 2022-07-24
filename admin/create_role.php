
<?php
    // require_once '../misc/database_auth.php';
    // require_once '../controls/project_control.php';
    // $projectControl = new ProjectControl($con);
    // $projects = $projectControl->getProjectsList();
    // $projectsHtml = $projectControl->projectAdminListTemplate($projects);
    $url = $_SERVER['REQUEST_URI'];
?>

<?php
    $pageTitle = "NLA KPI Admin | Create Role";
    require_once 'admin_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Create Role</h1>
                <section>
                    <div>
                        <label for="role" class="required">Role</label>
                        <input type="text" class="form-control" id="role">
                        <div class="invalid-feedback">
                            Please provide a role.
                        </div>
                    </div>
                </section>
                <?php echo $url ?>
                <div class="text-center my-5">
                    <button type="button" id="save-role" class="btn btn-1 btn-lg">Save Role</button>
                </div>
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>