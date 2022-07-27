<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    require_once '../controls/role_control.php';
    require_once '../controls/employee_control.php';

    $roleControl = new RoleControl($con);
    $projectControl = new ProjectControl($con);
    $employeeControl = new EmployeeControl($con);

    if(isset($_POST) and $_POST['task'] == 'saveProject'){
        echo json_encode("God is good");
    }

    // Employee
    elseif (isset($_POST) and $_POST['task'] == 'saveEmployee') {
        $employeeControl->saveEmployee();
    }
    elseif (isset($_POST) and $_POST['task'] == 'editEmployee') {
        $employeeControl->editEmployee();
    }
    elseif (isset($_POST) and $_POST['task'] == 'deleteEmployee') {
        $employeeControl->deleteEmployee();
    }

    // Role
    elseif (isset($_POST) and $_POST['task'] == 'saveRole') {
        $roleControl->saveRole();
    }
    elseif (isset($_POST) and $_POST['task'] == 'editRole') {
        $roleControl->editRole();
    }
    elseif (isset($_POST) and $_POST['task'] == 'deleteRole') {
        $roleControl->deleteRole();
    }
?>