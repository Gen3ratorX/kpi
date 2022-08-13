<?php
    require_once '../misc/database_auth.php';
    require_once '../controls/task_control.php';
    $taskControl = new TaskControl($con);

    // Task
    if(isset($_POST['task']) and $_POST['task'] == 'saveTask'){
        $taskControl->saveTask();
    }
    elseif(isset($_POST['task']) and $_POST['task'] == 'editTask'){
        $taskControl->editTask();
    }
    elseif(isset($_POST['task']) and $_POST['task'] == 'deleteTask'){
        $taskControl->deleteTask();
    }
    elseif(isset($_GET['task']) and $_GET['task'] == 'getTaskAssessments'){
        $taskControl->getTaskAssessments();
    }
    else{
        echo json_encode("God is good all the time");
    }
?>