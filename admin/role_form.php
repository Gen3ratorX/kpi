<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/role_control.php';
?>

<?php
    $roleId = $_GET['id'] ?? '';
    $roleName = "";
    // Check if we are editing
    $roleControl = new RoleControl($con);
    $role = $roleControl->getRole($roleId);
    $roleName = $role ? $role['name'] : '';
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
                        <input type="text" value="<?php echo $roleName; ?>" class="form-control" id="role">
                        <div class="invalid-feedback">
                            Please provide a role.
                        </div>
                    </div>
                </section>
                <div class="text-center my-5">
                    <button type="button" id="save-role" data-role-edit=<?php echo $roleName ? 'true' : 'false' ; ?> data-role-id="<?php echo $roleName ? $roleId : '' ?>" class="btn btn-1 btn-lg">Save Role</button>
                </div>
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>