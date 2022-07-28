<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/unit_control.php';
    require_once '../controls/department_control.php';
    $departmentControl = new DepartmentControl($con);
    $departments = $departmentControl->getDepartmentsList();
    $unitControl = new UnitControl($con);
    $departmentValues = $unitControl->generateDepartmentOptions($departments);
?>

<?php
    $unitId = $_GET['id'] ?? '';
    $unitName = "";
    // Check if we are editing
    if($unitId){
        $unit = $unitControl->getUnit($unitId);
        if($unit){
            $unitName = $unit['name'];
            $departmentId = $unit['department_id'];
            $departmentValues = $unitControl->generateDepartmentOptions($departments,$departmentId);
        }
    }
?>

<?php
    $pageTitle = "NLA KPI Admin | Create Unit";
    require_once 'admin_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Create Unit</h1>
                <section>
                    <div>
                        <label for="unit" class="required">Unit:</label>
                        <input type="text" value="<?php echo $unitName; ?>" class="form-control" id="unit">
                        <div class="invalid-feedback">
                            Please provide a unit.
                        </div>
                    </div>
                    <div class="my-4">
                        <label for="department" class="required">Department:</label>
                        <select id="department" class="form-control">
                            <?php echo $departmentValues; ?>
                        </select>
                    </div>
                </section>
                <div class="text-center my-5">
                    <button type="button" id="save-unit" data-unit-edit=<?php echo $unitName ? 'true' : 'false' ; ?> data-unit-id="<?php echo $unitName ? $unitId : '' ?>" class="btn btn-1 btn-lg">Save Unit</button>
                </div>
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>