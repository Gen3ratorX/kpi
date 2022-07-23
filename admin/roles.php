
<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/role_control.php';

    $roleControl = new RoleControl($con);
    $rolesHtml = $roleControl->roleAdminListTemplate();
?>

<?php
    $pageTitle = "NLA KPI Admin | Roles";
    require_once './admin_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Roles</h1>
                <?php echo $rolesHtml; ?>
            </section>
        </div>
    </main>
<?php
    require_once './admin_footer.php';
?>