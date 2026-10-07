<?php
    require_once '../misc/employee_login_required.php';
    require_once '../misc/database_auth.php';
    require_once '../controls/task_control.php';
    
    // Set header to return JSON
    header('Content-Type: application/json');
    
    $taskControl = new TaskControl($con);

    // Who is acting always comes from the session, never from the request
    $sessionEmployeeId = (int)$_SESSION['employeeId'];
    $sessionRole = (int)$_SESSION['employeeRole'];

    function forbidden()
    {
        http_response_code(403);
        echo json_encode(['status' => 'ERROR', 'message' => 'You are not allowed to do this']);
        exit;
    }

    // Tasks can only be added to projects the employee works on, and only changed by their owner
    function authorizeTaskChange($taskControl, $sessionEmployeeId, $sessionRole, $taskId)
    {
        $_POST['employeeId'] = $sessionEmployeeId;
        if ($taskId > 0) {
            $existingTask = $taskControl->getTask($taskId);
            if (!$existingTask || $existingTask['employee_id'] != $sessionEmployeeId) {
                forbidden();
            }
            $_POST['projectId'] = $existingTask['project_id'];
        }
        $projectId = isset($_POST['projectId']) ? (int)$_POST['projectId'] : 0;
        $_POST['projectId'] = $projectId;
        if (!$taskControl->canManageTasks($sessionRole, $sessionEmployeeId, $projectId)) {
            forbidden();
        }
    }

    // ===== HANDLE POST REQUESTS =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['task'])) {
            $task = $_POST['task'];
            
            switch($task) {
                case 'saveTask':
                    authorizeTaskChange($taskControl, $sessionEmployeeId, $sessionRole, 0);
                    $taskControl->saveTask();
                    break;
                    
                case 'saveTaskWithFile':
                    $taskId = isset($_POST['taskId']) ? (int)$_POST['taskId'] : 0;
                    authorizeTaskChange($taskControl, $sessionEmployeeId, $sessionRole, $taskId);
                    $taskControl->saveTaskWithFile();
                    break;
                    
                case 'editTask':
                    $taskId = isset($_POST['taskId']) ? (int)$_POST['taskId'] : 0;
                    if ($taskId <= 0) {
                        forbidden();
                    }
                    $_POST['taskId'] = $taskId;
                    authorizeTaskChange($taskControl, $sessionEmployeeId, $sessionRole, $taskId);
                    $taskControl->editTask();
                    break;
                    
                case 'deleteTask':
                    // Same parameter order deleteTask() reads
                    $taskId = (int)($_POST['task_id'] ?? $_POST['taskId'] ?? $_POST['id'] ?? 0);
                    if ($taskId <= 0) {
                        forbidden();
                    }
                    $_POST['task_id'] = $taskId;
                    authorizeTaskChange($taskControl, $sessionEmployeeId, $sessionRole, $taskId);
                    $taskControl->deleteTask();
                    break;
                    
                case 'saveAssessment':
                    $taskId = isset($_POST['taskId']) ? (int)$_POST['taskId'] : 0;
                    $assessedTask = $taskControl->getTask($taskId);
                    if (!$assessedTask || !$taskControl->canAssessEmployee($sessionRole, $sessionEmployeeId, $assessedTask['project_id'], $assessedTask['employee_id'])) {
                        forbidden();
                    }
                    $assessmentId = isset($_POST['assessmentId']) ? (int)$_POST['assessmentId'] : 0;
                    if ($assessmentId > 0) {
                        // Only the assessor who made a rating may update it
                        if (!$taskControl->isOwnAssessment($sessionEmployeeId, $taskId, $assessmentId)) {
                            forbidden();
                        }
                    } elseif ($taskControl->taskAlreadyAssessed($sessionRole, $sessionEmployeeId, $taskId)) {
                        forbidden();
                    }
                    $_POST['taskId'] = $taskId;
                    $_POST['assessmentId'] = $assessmentId ?: '';
                    $_POST['assessorId'] = $sessionEmployeeId;
                    $_POST['employeeId'] = $assessedTask['employee_id'];
                    $_POST['employeeRole'] = $sessionRole;
                    $taskControl->saveAssessment();
                    break;
                    
                case 'closeAssessment':
                    $projectId = isset($_POST['projectId']) ? (int)$_POST['projectId'] : 0;
                    // Only auditors and the general manager may close assessment
                    if ($sessionRole != 1 && $sessionRole != 0) {
                        forbidden();
                    }
                    if ($projectId > 0) {
                        if ($con->execute_query("UPDATE project SET assess = 0 WHERE id = ?", [$projectId])) {
                            echo json_encode(['status' => 'SUCCESS']);
                        } else {
                            http_response_code(500);
                            echo json_encode(['status' => 'ERROR', 'message' => $con->error]);
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(['status' => 'ERROR', 'message' => 'Invalid project ID']);
                    }
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode([
                        'status' => 'ERROR', 
                        'message' => 'Invalid task'
                    ]);
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 'ERROR', 
                'message' => 'No task specified'
            ]);
        }
    }
    // ===== HANDLE GET REQUESTS =====
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if(isset($_GET['task']) && $_GET['task'] == 'getTaskAssessments') {
            // Make sure required parameters are set
            $employeeId = isset($_GET['employeeId']) ? (int)$_GET['employeeId'] : 0;
            $projectId = isset($_GET['projectId']) ? (int)$_GET['projectId'] : 0;
            $assessorId = $sessionEmployeeId;
            $readOnly = isset($_GET['readOnly']) ? $_GET['readOnly'] : 'false';
            $employeeRole = $sessionRole;
            $assessorRole = isset($_GET['assessorRole']) ? (int)$_GET['assessorRole'] : 0;

            // Only employees this user may assess (or their own ratings) can be viewed
            if (!$taskControl->canAssessEmployee($sessionRole, $sessionEmployeeId, $projectId, $employeeId, true)) {
                forbidden();
            }
            // getTaskAssessments() reads from $_GET
            $_GET['employeeId'] = $employeeId;
            $_GET['projectId'] = $projectId;
            $_GET['assessorId'] = $assessorId;
            $_GET['employeeRole'] = $employeeRole;
            
            // Set the POST data for the method
            $_POST['employeeId'] = $employeeId;
            $_POST['projectId'] = $projectId;
            $_POST['assessorId'] = $assessorId;
            $_POST['readOnly'] = $readOnly;
            $_POST['employeeRole'] = $employeeRole;
            $_POST['assessorRole'] = $assessorRole;
            
            $taskControl->getTaskAssessments();
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 'ERROR', 
                'message' => 'Invalid GET request'
            ]);
        }
    }
    else {
        http_response_code(405);
        echo json_encode([
            'status' => 'ERROR', 
            'message' => 'Method not allowed. Use POST or GET.',
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
    }
?>