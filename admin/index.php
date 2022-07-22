
<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    $projectControl = new ProjectControl($con);
    $projects = $projectControl->getProjectsList();
    $projectsHtml = $projectControl->projectAdminListTemplate($projects);
?>

<?php
    $pageTitle = "NLA KPI Admin | Home";
    require_once 'admin_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Projects</h1>
                <?php echo $projectsHtml; ?>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>