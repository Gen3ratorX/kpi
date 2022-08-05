<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/role_control.php';
    $roleControl = new RoleControl($con);
?>

<?php
    $roleId = $_GET['id'] ?? '';
    $roleName = "";
    $roleNumber = "";
    // Check if we are editing
    if($roleId){
        $role = $roleControl->getRole($roleId);
        if($role){
            $roleName = $role['name'];
            $roleNumber = $role['role'];
        }
    }
?>

<?php
    $pageTitle = "NLA KPI Admin | Create Role";
    require_once 'admin_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Create Role</h1>
                <section class="row">
                    <div class="col">
                        <label for="name" class="required">Name:</label>
                        <input type="text" value="<?php echo $roleName; ?>" class="form-control" id="name">
                        <div class="invalid-feedback">
                            Please provide a role.
                        </div>
                    </div>
                    <div class="col-12 col-sm-auto">
                        <label for="role" class="required">Role Number:</label>
                        <input type="number" min='0' value="<?php echo $roleNumber;?>" class="form-control" id="role">
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