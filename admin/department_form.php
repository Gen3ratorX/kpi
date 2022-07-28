<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/department_control.php';
    $departmentControl = new DepartmentControl($con);
?>

<?php
    $departmentId = $_GET['id'] ?? '';
    $departmentName = "";
    // // Check if we are editing
    if($departmentId){
        $department = $departmentControl->getDepartment($departmentId);
        if($department){
            $departmentName = $department['name'];
            $departmentHeadName = $department['departmentHeadName'] ?? '';
            $departmentHeadId = $department['employee_id'];
        }
            
    }
?>

<?php
    $pageTitle = "NLA KPI Admin | Create Department";
    require_once 'admin_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Create Department</h1>
                <section>
                    <div>
                        <label for="role" class="required">Department:</label>
                        <input type="text" value="<?php echo $departmentName; ?>" class="form-control" id="department">
                        <div class="invalid-feedback">
                            Please provide a department.
                        </div>
                    </div>
                    <div class="my-3">
                        <label for="" class="required">Department Head:</label>
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <input id="search-input" placeholder="Search for employees...." type="search" class="form-control">
                            </div>
                            <div class="col-auto">
                                <button id="search-department-head" type="button" class="btn btn-3 btn-sm">Search</button>
                            </div>
                        </div>
                        <div id="search-results" class="row my-3 g-2">
                            <?php 
                                $departmentId and isset($department['departmentHeadName'])
                                ? $departmentControl->generateDefaultDepartmentHeadContent(
                                    $departmentId,$department
                                )
                                : $departmentControl->generateDefaultDepartmentHeadContent(); 
                            ?>
                        </div>
                    </div>
                </section>
                <div class="text-center my-5">
                    <button type="button" id="save-department" data-department-edit=<?php echo $departmentName ? 'true' : 'false' ; ?> data-department-id="<?php echo $departmentName ? $departmentId : '' ?>" class="btn btn-1 btn-lg">Save Department</button>
                </div>
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>