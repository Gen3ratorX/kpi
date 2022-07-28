<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    require_once '../controls/role_control.php';
    require_once '../controls/employee_control.php';
    require_once '../controls/department_control.php';
    require_once '../controls/unit_control.php';

    $roleControl = new RoleControl($con);
    $projectControl = new ProjectControl($con);
    $employeeControl = new EmployeeControl($con);
    $departmentControl = new DepartmentControl($con);
    $unitControl = new UnitControl($con);

    if(isset($_POST['task']) and $_POST['task'] == 'saveProject'){
        echo json_encode("God is good");
    }

    // Employee
    elseif (isset($_POST['task']) and $_POST['task'] == 'saveEmployee') {
        $employeeControl->saveEmployee();
    }
    elseif (isset($_POST['task']) and $_POST['task'] == 'editEmployee') {
        $employeeControl->editEmployee();
    }
    elseif (isset($_POST['task']) and $_POST['task'] == 'deleteEmployee') {
        $employeeControl->deleteEmployee();
    }

    // Role
    elseif (isset($_POST['task']) and $_POST['task'] == 'saveRole') {
        $roleControl->saveRole();
    }
    elseif (isset($_POST['task']) and $_POST['task'] == 'editRole') {
        $roleControl->editRole();
    }
    elseif (isset($_POST['task']) and $_POST['task'] == 'deleteRole') {
        $roleControl->deleteRole();
    }

    // Department
    elseif (isset($_POST['task']) and $_POST['task'] == 'saveDepartment') {
        $departmentControl->saveDepartment();
    }
    elseif (isset($_POST['task']) and $_POST['task'] == 'editDepartment') {
        $departmentControl->editDepartment();
    }
    elseif (isset($_POST['task']) and $_POST['task'] == 'deleteDepartment') {
        $departmentControl->deleteDepartment();
    }
    elseif (isset($_GET['task']) and $_GET['task'] == 'searchDepartmentHead') {
        $departmentControl->searchDepartmentHeads();
    } 

    // Unit
    elseif (isset($_POST['task']) and $_POST['task'] == 'saveUnit') {
        $unitControl->saveUnit();
    }
    elseif (isset($_POST['task']) and $_POST['task'] == 'editUnit') {
        $unitControl->editUnit();
    }
    elseif (isset($_POST['task']) and $_POST['task'] == 'deleteUnit') {
        $unitControl->deleteUnit();
    }
?>