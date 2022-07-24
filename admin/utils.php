<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    require_once '../controls/role_control.php';

    $roleControl = new RoleControl($con);
    $projectControl = new ProjectControl($con);

    if(isset($_POST) and $_POST['task'] == 'saveProject'){
        echo json_encode("God is good");
    }
    elseif (isset($_POST) and $_POST['task'] == 'saveEmployee') {
        echo json_encode("God is very good");
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