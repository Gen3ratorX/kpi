<?php
require_once '../misc/utils.php';
class TaskControl
{
    private $con;

    function __construct($con)
    {
        $this->con = $con;
    }

    function getTaskList($employeeId, $projectId, $q = null, $columns = null)
    {
        $tasks = [];
        $results1 = $this->con->execute_query("SELECT * FROM task WHERE project_id=? AND employee_id=?", [$projectId, $employeeId]);
        while ($row1 = $results1->fetch_assoc()) {
            $tasks[] = $row1;
        }
        return $tasks;
    }

    // ===== ADD THIS METHOD =====
    function getTaskFiles($taskId)
    {
        $files = [];
        $result = $this->con->execute_query("SELECT * FROM task_files WHERE task_id = ?", [$taskId]);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $files[] = $row;
            }
        }
        return $files;
    }

    function generateEmployeeTasksHtml($employeeId, $projectId)
    {
        $tasksHtml = "";
        $tasks = $this->getTaskList($employeeId, $projectId);
        if ($tasks) {
            foreach ($tasks as $task) {
                $taskId = $task['id'];
                $taskDescription = $task['description'];
                
                // Get attached files
                $files = $this->getTaskFiles($taskId);
                $filesHtml = "";
                if ($files) {
                    foreach ($files as $file) {
                        $fileSize = round($file['file_size'] / 1024 / 1024, 2);
                        $filesHtml .= "
                            <div class='task-attachment' data-file-id='{$file['id']}'>
                                <i class='bi bi-file-earmark-fill'></i>
                                <div class='file-info'>
                                    <div class='file-name'>{$file['file_name']}</div>
                                    <div class='file-size'>{$fileSize} MB</div>
                                </div>
                                <a href='../uploads/tasks/{$file['file_path']}' class='btn-download' download>
                                    <i class='bi bi-download'></i> Download
                                </a>
                            </div>
                        ";
                    }
                }
                
                $tasksHtml .= "
                    <div class='task-card' id='task-{$taskId}'>
                        <div class='task-header'>
                            <div class='task-actions'>
                                <button class='btn-action btn-edit edit-task' data-task-id='{$taskId}' data-bs-toggle='modal' data-bs-target='#addItem'>
                                    <i class='bi bi-pencil-fill'></i> Edit
                                </button>
                                <button class='btn-action btn-delete delete-task-attempt' id='task-{$taskId}' data-bs-toggle='modal' data-bs-target='#deleteItem'>
                                    <i class='bi bi-trash3-fill'></i> Delete
                                </button>
                            </div>
                        </div>
                        <div class='task-content'>
                            {$taskDescription}
                        </div>
                        {$filesHtml}
                        <div class='task-meta'>
                            <span><i class='bi bi-calendar3'></i> Created: " . date('M d, Y', strtotime($task['date_created'])) . "</span>
                            <span><i class='bi bi-calendar-check'></i> Deadline: " . date('M d, Y', strtotime($task['deadline'])) . "</span>
                        </div>
                    </div>
                ";
            }
        } else {
            $tasksHtml .= "
                <div class='empty-tasks'>
                    <i class='bi bi-clipboard-plus'></i>
                    <h4>No Tasks Added</h4>
                    <p>You haven't added any tasks to this project yet.</p>
                    <button class='btn-add-task' data-bs-toggle='modal' data-bs-target='#addItem'>
                        <i class='bi bi-plus-circle-fill'></i> Add Your First Task
                    </button>
                </div>
            ";
        }
        return $tasksHtml;
    }

    function saveTask()
    {
        $employeeId = filterInput('employeeId');
        $projectId = filterInput('projectId');
        $employeeTask = filterInput('employeeTask');
        $currentDate = date("Y-m-d");
        // Get project deadline
        $result1 = $this->con->execute_query("SELECT deadline FROM project WHERE id=?", [$projectId]);
        if ($result1->num_rows == 1) {
            $deadline = $result1->fetch_assoc()['deadline'];
            // Insert task
            $sql2 = "INSERT INTO task(project_id,employee_id,description,date_created,deadline) VALUE(?,?,?,?,?)";
            if ($this->con->execute_query($sql2, [$projectId, $employeeId, $employeeTask, $currentDate, $deadline])) {
                // The id of the task just inserted (not "the newest row", which another save could change)
                echo json_encode([
                    'status' => "SUCCESS",
                    'taskId' => $this->con->insert_id,
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'ERROR']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'ERROR', 'info' => 'deadline']);
        }
    }

    function saveTaskWithFile()
    {
        // Get data from POST
        $employeeId = isset($_POST['employeeId']) ? (int)$_POST['employeeId'] : 0;
        $projectId = isset($_POST['projectId']) ? (int)$_POST['projectId'] : 0;
        $employeeTask = filterInput('employeeTask') ?? '';
        $taskId = isset($_POST['taskId']) ? (int)$_POST['taskId'] : 0;
        $removeFile = isset($_POST['removeFile']) ? (int)$_POST['removeFile'] : 0;
        
        $currentDate = date("Y-m-d");
        
        // Validate
        if ($employeeId <= 0 || $projectId <= 0 || empty($employeeTask)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'ERROR', 
                'message' => 'Missing required fields'
            ]);
            return;
        }
        
        // ===== HANDLE FILE UPLOAD =====
        $fileData = null;
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            // Create upload directory
            $uploadDir = '../uploads/tasks/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $file = $_FILES['file'];
            
            // Validate file size (10MB max)
            if ($file['size'] > 10 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['status' => 'ERROR', 'message' => 'File size exceeds 10MB limit']);
                return;
            }
            
            // Validate the type from the extension and the file's real contents, never the
            // browser-supplied type: anyone can label a .php file as image/png and have it run
            $allowedTypes = [
                'pdf' => ['application/pdf'],
                'doc' => ['application/msword', 'application/CDFV2', 'application/x-ole-storage'],
                'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
                'xls' => ['application/vnd.ms-excel', 'application/CDFV2', 'application/x-ole-storage'],
                'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
                'png' => ['image/png'],
                'jpg' => ['image/jpeg'],
                'jpeg' => ['image/jpeg'],
            ];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $detectedType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if (!isset($allowedTypes[$extension]) || !in_array($detectedType, $allowedTypes[$extension], true)) {
                http_response_code(400);
                echo json_encode(['status' => 'ERROR', 'message' => 'Invalid file type. Allowed: PDF, Word, Excel, PNG, JPG']);
                return;
            }
            
            // Generate unique filename, keeping only the checked extension
            $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME)) ?: 'file';
            $fileName = time() . '_' . $baseName . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $fileData = [
                    'name' => htmlspecialchars($file['name']),
                    'path' => $fileName,
                    'size' => (int)$file['size'],
                    'type' => $detectedType
                ];
            }
        }
        
        // Get project deadline
        $result1 = $this->con->execute_query("SELECT deadline FROM project WHERE id = ?", [$projectId]);
        
        if (!$result1 || $result1->num_rows == 0) {
            http_response_code(500);
            echo json_encode(['status' => 'ERROR', 'message' => 'Project not found']);
            return;
        }
        
        $deadline = $result1->fetch_assoc()['deadline'];
        
        if ($taskId > 0) {
            // ===== UPDATE EXISTING TASK =====
            if ($this->con->execute_query("UPDATE task SET description = ? WHERE id = ?", [$employeeTask, $taskId])) {
                // Handle file operations for update
                if ($fileData) {
                    // Add new file
                    $this->saveTaskFile($taskId, $fileData);
                } elseif ($removeFile) {
                    // Delete existing file
                    $result4 = $this->con->execute_query("SELECT file_path FROM task_files WHERE task_id = ?", [$taskId]);
                    if ($result4) {
                        while ($row4 = $result4->fetch_assoc()) {
                            $fileToDelete = '../uploads/tasks/' . $row4['file_path'];
                            if (file_exists($fileToDelete)) {
                                unlink($fileToDelete);
                            }
                        }
                    }
                    $this->con->execute_query("DELETE FROM task_files WHERE task_id = ?", [$taskId]);
                }
                
                echo json_encode(['status' => 'SUCCESS', 'taskId' => $taskId]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'ERROR']);
            }
        } else {
            // ===== INSERT NEW TASK =====
            $sql2 = "INSERT INTO task (project_id, employee_id, description, date_created, deadline) VALUES (?, ?, ?, ?, ?)";
            if ($this->con->execute_query($sql2, [$projectId, $employeeId, $employeeTask, $currentDate, $deadline])) {
                $newTaskId = $this->con->insert_id;
                
                // Save file if uploaded
                if ($fileData) {
                    $this->saveTaskFile($newTaskId, $fileData);
                }
                
                echo json_encode(['status' => 'SUCCESS', 'taskId' => $newTaskId]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'ERROR']);
            }
        }
    }

    function saveTaskFile($taskId, $fileData)
    {
        $this->con->execute_query(
            "INSERT INTO task_files (task_id, file_name, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?)",
            [$taskId, $fileData['name'], $fileData['path'], $fileData['size'], $fileData['type']]
        );
    }

    function editTask()
    {
        $taskId = filterInput("taskId");
        $employeeTask = filterInput("employeeTask");
        $projectId = filterInput("projectId");
        $employeeId = filterInput("employeeId");

        if ($this->con->execute_query("UPDATE task SET description=? WHERE id=?", [$employeeTask, $taskId])) {
            echo json_encode(['status' => 'SUCCESS']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'ERROR']);
        }
    }

    function deleteTask()
    {
        // Get taskId from POST - try multiple parameter names
        $taskId = 0;
        
        if (isset($_POST['task_id'])) {
            $taskId = (int)$_POST['task_id'];
        } elseif (isset($_POST['taskId'])) {
            $taskId = (int)$_POST['taskId'];
        } elseif (isset($_POST['id'])) {
            $taskId = (int)$_POST['id'];
        }
        
        if ($taskId <= 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'ERROR', 
                'message' => 'Invalid Task ID'
            ]);
            return;
        }
        
        // Check if task exists
        $checkResult = $this->con->execute_query("SELECT id FROM task WHERE id = ?", [$taskId]);
        
        if (!$checkResult || $checkResult->num_rows == 0) {
            http_response_code(404);
            echo json_encode(['status' => 'ERROR', 'message' => 'Task not found']);
            return;
        }
        
        // Delete the task
        if ($this->con->execute_query("DELETE FROM task WHERE id = ?", [$taskId])) {
            echo json_encode(['status' => 'SUCCESS']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'ERROR']);
        }
    }

    // Permissions
    function getTask($taskId)
    {
        $result1 = $this->con->execute_query("SELECT id, employee_id, project_id FROM task WHERE id=?", [(int)$taskId]);
        return ($result1 && $result1->num_rows == 1) ? $result1->fetch_assoc() : null;
    }

    function getProjectState($projectId)
    {
        $result1 = $this->con->execute_query("SELECT is_open, assess FROM project WHERE id=?", [(int)$projectId]);
        return ($result1 && $result1->num_rows == 1) ? $result1->fetch_assoc() : null;
    }

    // Mirrors employee/project_detail.php: auditors don't add tasks, and tasks are
    // locked once the project is closed or under assessment
    function canManageTasks($employeeRole, $employeeId, $projectId)
    {
        $project = $this->getProjectState($projectId);
        return $project
            && $employeeRole != 1
            && $project['is_open'] && !$project['assess']
            && $this->isEmployeeAssignedToProject($employeeRole, (int)$employeeId, (int)$projectId);
    }

    // An assessor may only rate the employees getEmployeesForProject() lists for them.
    // Read-only entries (other assessors' ratings of yourself) can be viewed, not rated.
    function canAssessEmployee($assessorRole, $assessorId, $projectId, $employeeId, $viewOnly = false)
    {
        $project = $this->getProjectState($projectId);
        if (!$project || !$this->isEmployeeAssignedToProject($assessorRole, (int)$assessorId, (int)$projectId)) {
            return false;
        }
        // Auditors can assess at any time, everyone else only while the project is under assessment
        if (!$viewOnly && $assessorRole != 1 && !$project['assess']) {
            return false;
        }
        foreach ($this->getEmployeesForProject($assessorRole, (int)$projectId, (int)$assessorId) as $employee) {
            if ($employee['id'] == $employeeId && ($viewOnly || empty($employee['readOnly']))) {
                return true;
            }
        }
        return false;
    }

    // True when this assessor has already rated the task. Only one auditor and one
    // general manager may rate each task, so for them any rating by that role counts.
    function taskAlreadyAssessed($assessorRole, $assessorId, $taskId)
    {
        if ($assessorRole == 1 || $assessorRole == 0) {
            $result1 = $this->con->execute_query("SELECT id FROM performance WHERE task_id=? AND assessor_id IN 
                (SELECT id FROM employee WHERE employee_role_id IN 
                (SELECT id FROM employee_role WHERE role=?))", [(int)$taskId, (int)$assessorRole]);
        } else {
            $result1 = $this->con->execute_query("SELECT id FROM performance WHERE task_id=? AND assessor_id=?", [(int)$taskId, (int)$assessorId]);
        }
        return $result1 && $result1->num_rows > 0;
    }

    function isOwnAssessment($assessorId, $taskId, $assessmentId)
    {
        $result1 = $this->con->execute_query("SELECT id FROM performance WHERE id=? AND task_id=? AND assessor_id=?",
            [(int)$assessmentId, (int)$taskId, (int)$assessorId]);
        return $result1 && $result1->num_rows == 1;
    }

    // Assessments
    function isEmployeeAssignedToProject($employeeRole, $employeeId, $projectId)
    {
        // TODO: Special role
        // Auditor or General Manager
        if ($employeeRole != 1 and $employeeRole != 0) {
            $result1 = $this->con->execute_query("SELECT * FROM assign WHERE project_id=? AND employee_id=?", [$projectId, $employeeId]);
            return $result1->num_rows == 1 ? true : false;
        }
        return true;
    }

    function getEmployeesForProject($employeeRole, $projectId, $employeeId)
    {
        $employees = [];
        // TODO: Special Roles
        // Auditor or General Manager
        if ($employeeRole == 1 or $employeeRole == 0) {
            $sql1 = "SELECT CONCAT_WS(' ',surname,other_names) as name, id FROM employee 
                WHERE id IN 
                (SELECT employee_id FROM assign WHERE project_id=?)";
            $results1 = $this->con->execute_query($sql1, [$projectId]);
            while ($row1 = $results1->fetch_assoc()) {
                $employees[] = $row1;
            }
        }
        // Manager (role 3; role 2 is a regular employee)
        elseif ($employeeRole == 3) {
            // Get the managers department id
            $result1 = $this->con->execute_query("SELECT department_id FROM employee WHERE id=?", [$employeeId]);
            if ($result1->num_rows == 1) {
                $departmentId = $result1->fetch_assoc()['department_id'];
                // Get all employees who are part of the department and the project
                $sql2 = "SELECT CONCAT_WS(' ',surname,other_names) as name, id FROM `employee` 
                    WHERE department_id IN (SELECT id FROM department WHERE id=?) 
                    AND id IN (SELECT employee_id FROM assign WHERE project_id=?)";
                $results2 = $this->con->execute_query($sql2, [$departmentId, $projectId]);
                while ($row2 = $results2->fetch_assoc()) {
                    $row2['readOnly'] = false;
                    // Identify the manager in the list of employees
                    if ($employeeId == $row2['id']) {
                        $employeeName = $row2['name'];
                        $row2['name'] = $employeeName . ' (Yourself)';
                    }
                    $employees[] = $row2;
                }
                // Insert default members
                $employees[] = [
                    'name' => 'Auditor',
                    'readOnly' => true,
                    'role' => 1,
                    'id' => $employeeId,
                    'assessorRole' => 1
                ];
                $employees[] = [
                    'name' => 'General Manager',
                    'readOnly' => true,
                    'role' => 0,
                    'id' => $employeeId,
                    'assessorRole' => 0,
                ];
            }
        } else {
            $sql1 = "SELECT CONCAT_WS(' ',surname,other_names) as name, id FROM employee 
                WHERE id IN 
                (SELECT employee_id FROM assign WHERE project_id=? AND employee_id=?)";
            $result1 = $this->con->execute_query($sql1, [$projectId, $employeeId]);
            if ($result1->num_rows == 1) {
                $row1 = $result1->fetch_assoc();
                $row1['readOnly'] = false;
                if ($employeeId == $row1['id']) {
                    $employeeName = $row1['name'];
                    $row1['name'] = $employeeName . ' (Yourself)';
                }
                $employees[] = $row1;

                // Insert default members
                $employees[] = [
                    'name' => 'Auditor',
                    'readOnly' => true,
                    'role' => 1,
                    'id' => $employeeId,
                    'assessorRole' => 1,
                ];
                $employees[] = [
                    'name' => 'Manager',
                    'readOnly' => true,
                    'role' => 3,
                    'id' => $employeeId,
                    'assessorRole' => 3,
                ];
                $employees[] = [
                    'name' => 'General Manager',
                    'readOnly' => true,
                    'role' => 0,
                    'id' => $employeeId,
                    'assessorRole' => 0,
                ];
            }
        }
        return $employees;
    }

    function generateEmployeesForProjectHtml($employeeRole, $projectId, $employeeId)
    {
        $employeesForProjectHtml = "";

        $employees = $this->getEmployeesForProject($employeeRole, $projectId, $employeeId);
        if ($employees) {
            foreach ($employees as $employee) {
                $readOnly = $employee['readOnly'] ?? '';
                $assessorRole = $employee['assessorRole'] ?? '';
                $employeesForProjectHtml .= "
                        <div class='col-auto'>
                            <div class='card shadow-sm item assess-employee' data-employee-id='{$employee['id']}' data-assessor-id='{$employeeId}' data-project-id='$projectId' data-read-only='$readOnly' data-employee-role='$employeeRole' data-assessor-role='{$assessorRole}'>
                                <div class='card-body'>
                                    {$employee['name']}
                                </div>
                            </div>
                        </div>
                    ";
            }
        } else {
            $employeesForProjectHtml = "header(Location: ./)";
        }
        return $employeesForProjectHtml;
    }

  function getTaskAssessments()
{
    $assessments = [];
    $employeeRole = filterInput('employeeRole', false);
    $readOnly = filter_var(filterInput('readOnly', false), FILTER_VALIDATE_BOOLEAN);
    $projectId = filterInput('projectId', false);
    $employeeId = filterInput('employeeId', false);
    $assessorId = filterInput('assessorId', false);
    $assessorRole = filterInput('assessorRole', false);
    $tasks = $this->getTaskList($employeeId, $projectId);
    $auditorHasAssessed = false;
    
    foreach ($tasks as $task) {
        $taskId = $task['id'];
        
        // ===== GET FILES FOR THIS TASK =====
        $files = $this->getTaskFiles($taskId);
        
        // TODO: Add general manager and others
        if ($employeeRole != 1 and $employeeRole != 0) { // All employees apart from auditors and general manager
            // Auditor or manager who has assessed an employee
            if ($readOnly) {
                // Get the performance for each task
                $result1 = $this->con->execute_query("SELECT * FROM performance WHERE task_id=? AND assessor_id IN 
                    (SELECT id FROM employee WHERE employee_role_id IN 
                    (SELECT id FROM employee_role WHERE role=?))", [$taskId, (int)$assessorRole]);
            } else {
                // Get the performance for each task
                $result1 = $this->con->execute_query("SELECT * FROM performance WHERE task_id=? AND assessor_id=?", [$taskId, $assessorId]);
            }
            if ($result1->num_rows == 1) {
                $assessment = $result1->fetch_assoc();
                $assessments[] = [
                    'isAssessed' => true,
                    'taskId' => $taskId,
                    'description' => $task['description'],
                    'performanceId' => $assessment['id'],
                    'comments' => $assessment['comments'],
                    'rating' => $assessment['rating'],
                    'assessmentId' => $assessment['id'],
                    'assessorId' => $assessorId,
                    'employeeId' => $employeeId,
                    'employeeRole' => $employeeRole,
                    'projectId' => $projectId,
                    'readOnly' => $readOnly ? true : false,
                    'files' => $files  // <-- ADDED: Include task files
                ];
            } else {
                $assessments[] = [
                    'isAssessed' => false,
                    'taskId' => $taskId,
                    'description' => $task['description'],
                    'assessorId' => $assessorId,
                    'employeeId' => $employeeId,
                    'employeeRole' => $employeeRole,
                    'projectId' => $projectId,
                    'readOnly' => $readOnly ? true : false,
                    'files' => $files  // <-- ADDED: Include task files
                ];
            }
        }
        // TODO: Special role
        // Auditor or General Manager
        else {
            $result1 = $this->con->execute_query("SELECT * FROM performance WHERE task_id=? 
                AND assessor_id IN 
                (SELECT id FROM employee WHERE employee_role_id IN 
                (SELECT id FROM employee_role WHERE role=?))", [$taskId, (int)$employeeRole]);
            // No auditor has assessed the employee
            if ($result1->num_rows == 0) {
                $assessments[] = [
                    'isAssessed' => false,
                    'taskId' => $taskId,
                    'description' => $task['description'],
                    'assessorId' => $assessorId,
                    'employeeId' => $employeeId,
                    'employeeRole' => $employeeRole,
                    'projectId' => $projectId,
                    'readOnly' => $auditorHasAssessed ? true : false,
                    'files' => $files  // <-- ADDED: Include task files
                ];
            }
            // An auditor has assessed an employee
            elseif ($result1->num_rows == 1) {
                $assessment = $result1->fetch_assoc();
                // The same auditor has assessed the employee
                if ($assessment['assessor_id'] == $assessorId) {
                    $assessments[] = [
                        'isAssessed' => true,
                        'taskId' => $taskId,
                        'description' => $task['description'],
                        'performanceId' => $assessment['id'],
                        'comments' => $assessment['comments'],
                        'rating' => $assessment['rating'],
                        'assessmentId' => $assessment['id'],
                        'assessorId' => $assessorId,
                        'employeeId' => $employeeId,
                        'employeeRole' => $employeeRole,
                        'projectId' => $projectId,
                        'readOnly' => false,
                        'files' => $files  // <-- ADDED: Include task files
                    ];
                }
                // A different auditor has assessed the employee
                else {
                    $auditorHasAssessed = true;
                    $assessments[] = [
                        'isAssessed' => true,
                        'taskId' => $taskId,
                        'description' => $task['description'],
                        'performanceId' => $assessment['id'],
                        'comments' => $assessment['comments'],
                        'rating' => $assessment['rating'],
                        'assessmentId' => $assessment['id'],
                        'assessorId' => $assessorId,
                        'employeeId' => $employeeId,
                        'employeeRole' => $employeeRole,
                        'projectId' => $projectId,
                        'readOnly' => true,
                        'files' => $files  // <-- ADDED: Include task files
                    ];
                }
            }
        }
    }
    echo json_encode([
        'status' => "SUCCESS",
        "assessments" => $assessments,
        'employeeRole' => $employeeRole
    ]);
}

    function saveAssessment()
    {
        $taskId = filterInput('taskId');
        $assessorId = filterInput('assessorId');
        $employeeId = filterInput('employeeId');
        $employeeRole = filterInput('employeeRole');
        $assessmentId = filterInput('assessmentId');
        $comments = filterInput('comments');
        $rating = filterInput('rating');

        $todaysDate = date('Y-m-d');
        // Task has been assessed already
        if ($assessmentId) {
            if ($this->con->execute_query("UPDATE performance SET comments=?, rating=? WHERE id=?", [$comments, $rating, $assessmentId])) {
                echo json_encode([
                    'status' => "SUCCESS"
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => "ERROR"]);
            }
        }
        // Task has not been assessed
        else {
            $sql1 = "INSERT INTO performance(task_id,assessor_id,date,rating,comments) VALUE(?,?,?,?,?)";
            // Insert data
            if ($this->con->execute_query($sql1, [$taskId, $assessorId, $todaysDate, $rating, $comments])) {
                echo json_encode([
                    'status' => "SUCCESS",
                    'assessmentId' => $this->con->insert_id,
                ]);
            }
            // Error inserting
            else {
                http_response_code(500);
                echo json_encode(['status' => "ERROR"]);
            }
        }
    }
    
}
?>