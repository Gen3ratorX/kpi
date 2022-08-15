<?php
    require_once '../misc/utils.php';
    class TaskControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
        }

        function getTaskList($employeeId,$projectId,$q=null,$columns=null){
            $tasks = [];
            $sql1 = "SELECT * FROM task WHERE project_id=$projectId AND employee_id=$employeeId";

            $results1 = $this->con->query($sql1);
            while($row1 = $results1->fetch_assoc()){
                $tasks[] = $row1;
            }

            return $tasks;
        }

        function generateEmployeeTasksHtml($employeeId,$projectId){
            $tasksHtml = "";
            $tasks = $this->getTaskList($employeeId,$projectId);
            if($tasks){
                foreach($tasks as $task){
                    $tasksHtml .= "
                        <div class='action-item card shadow-sm mb-3 task'>
                            <div class='options d-flex align-items-center justify-content-center'>
                                <div>
                                    <button class='btn btn-md btn-2 me-2 delete-task-attempt' id='task-{$task['id']}' data-bs-toggle='modal' data-bs-target='#deleteItem'>Delete</button>
                                    <button type='button' data-task-id='task-{$task['id']}' class='btn btn-md btn-4 edit-task' data-bs-toggle='modal' data-bs-target='#addItem'>Edit</button>
                                </div>
                            </div>
                            <div class='card-body' id='task-{$task['id']}'>
                                {$task['description']}
                            </div>
                        </div>
                    ";
                }
            }
            else{
                $tasksHtml .= "
                    <div class='no-item'>
                        No task has been added...
                        <div class='text-center'>
                            <button data-bs-toggle='modal' data-bs-target='#addItem' type='button' class='btn btn-md btn-1'>Add Task</button>
                        </div>
                    </div>
                ";
            }

            return $tasksHtml;
        }

        function saveTask(){
            $employeeId = filterInput('employeeId');
            $projectId = filterInput('projectId');
            $employeeTask = filterInput('employeeTask');
            $currentDate = date("Y-m-d");
            // Get project deadline
            $sql1 = "SELECT deadline FROM project WHERE id=$projectId";
            $result1 = $this->con->query($sql1);
            if($result1->num_rows == 1){
                $deadline = $result1->fetch_assoc()['deadline'];
                // Insert task
                $sql2 = "INSERT INTO task(project_id,employee_id,description,date_created,deadline) 
                VALUE($projectId,$employeeId,'$employeeTask','$currentDate','$deadline')";
                if($this->con->query($sql2)){
                    // Get the latest task inserted
                    $sql3 = "SELECT id FROM task ORDER BY id DESC LIMIT 1";
                    $result3 = $this->con->query($sql3);
                    if($result3->num_rows == 1){
                        $taskId = $result3->fetch_assoc()['id'];
                        echo json_encode([
                            'status'=>"SUCCESS",
                            'taskId'=> $taskId,
                        ]);
                    }
                    else{
                        http_response_code(500);
                        echo json_encode(['status'=>'ERROR','info'=>'didn"t get task id']);
                    }
                }
                else{
                    http_response_code(500);
                    echo json_encode(['status'=>'ERROR','info'=>$deadline]);
                }
            }
            else{
                http_response_code(500);
                echo json_encode(['status'=>'ERROR','info'=>'deadline']);
            }
        }
        
        function editTask(){
            $taskId = filterInput("taskId");
            $employeeTask = filterInput("employeeTask");
            $projectId = filterInput("projectId");
            $employeeId = filterInput("employeeId");
            
            $sql1 = "UPDATE task SET description='$employeeTask' WHERE id=$taskId";
            if($this->con->query($sql1)){
                echo json_encode(['status'=>'SUCCESS']);
            }
            else{
                http_response_code(500);
                echo json_encode(['status'=>'ERROR']);
            }
        }

        function deleteTask(){
            $taskId = filterInput('taskId');

            $sql1 = "DELETE FROM task WHERE id=$taskId";
            if($this->con->query($sql1)){
                echo json_encode(['status'=>'SUCCESS']);
            }
            else{
                http_response_code(500);
                echo json_encode(['status'=>'ERROR']);
            }
        }
        
        // Assessments
        function isEmployeeAssignedToProject($employeeRole,$employeeId,$projectId){
            if($employeeRole != 1){
                $sql1 = "SELECT * FROM assign WHERE project_id=$projectId AND employee_id=$employeeId";
                $result1 = $this->con->query($sql1);
                return $result1->num_rows == 1 ? true : false;
            }
            return true;
        }

        function getEmployeesForProject($employeeRole,$projectId,$employeeId){
            $employees = [];
            // Auditor
            if($employeeRole == 1){
                $sql1 = "SELECT CONCAT_WS(' ',surname,other_names) as name, id FROM employee 
                WHERE id IN 
                (SELECT employee_id FROM assign WHERE project_id=$projectId)";
                $results1 = $this->con->query($sql1);
                while($row1 = $results1->fetch_assoc()){
                    $employees[] = $row1; 
                }
            }
            elseif($employeeRole == 2){
                // Get the managers id
                $sql1 = "SELECT department_id FROM employee WHERE id=$employeeId";
                $result1 = $this->con->query($sql1);
                if($result1->num_rows == 1){
                    $departmentId = $result1->fetch_assoc()['department_id'];
                    // Get all employees who are part of the department and the project
                    $sql2 = "SELECT CONCAT_WS(' ',surname,other_names) as name, id FROM `employee` 
                    WHERE department_id IN (SELECT id FROM department WHERE id=$departmentId) 
                    AND id IN (SELECT employee_id FROM assign WHERE project_id=$projectId)";
                    $results2 = $this->con->query($sql2);
                    while($row2 = $results2->fetch_assoc()){
                        $row2['readOnly'] = false;
                        if($employeeId == $row2['id']){
                            $employeeName = $row2['name'];
                            $row2['name'] = $employeeName.' (Yourself)';
                        }
                        $employees[] = $row2;
                    }
                    // Insert default members
                    $employees[] = [
                        'name'=>'Auditor',
                        'readOnly'=> true,
                        'role'=> 1,
                        'id'=>$employeeId
                    ];
                }
            }
            else{
                $sql1 = "SELECT CONCAT_WS(' ',surname,other_names) as name, id FROM employee 
                WHERE id IN 
                (SELECT employee_id FROM assign WHERE project_id=$projectId AND employee_id=$employeeId)";
                $result1 = $this->con->query($sql1);
                if($result1->num_rows == 1){
                    $row1 = $result1->fetch_assoc();
                    $row1['readOnly'] = false;
                    if($employeeId == $row1['id']){
                        $employeeName = $row1['name'];
                        $row1['name'] = $employeeName.' (Yourself)';
                    }
                    $employees[] = $row1;

                    // Insert default members
                    $employees[] = [
                        'name'=>'Auditor',
                        'readOnly'=> true,
                        'role'=> 1,
                        'id'=>$employeeId
                    ];
                    $employees[] = [
                        'name'=>'Manager',
                        'readOnly'=> true,
                        'role'=> 2,
                        'id'=>$employeeId
                    ];
                }
            }
            return $employees;
        }

        function generateEmployeesForProjectHtml($employeeRole,$projectId,$employeeId){
            $employeesForProjectHtml = "";

            $employees = $this->getEmployeesForProject($employeeRole,$projectId,$employeeId);
            if($employees){
                foreach($employees as $employee){
                    $employeeRole = $employee['role'] ?? '';
                    $readOnly = $employee['readOnly'] ?? '';
                    $employeesForProjectHtml .= "
                        <div class='col-auto'>
                            <div class='card shadow-sm item assess-employee' data-employee-id='{$employee['id']}' data-assessor-id='{$employeeId}' data-project-id='$projectId' data-read-only='$readOnly' data-employee-role='$employeeRole'>
                                <div class='card-body'>
                                    {$employee['name']}
                                </div>
                            </div>
                        </div>
                    ";
                }
            }
            else{
                $employeesForProjectHtml = "header(Location: ./)";
            }
            return $employeesForProjectHtml;
        }

        function getTaskAssessments(){
            $assessments = [];
            $employeeRole = filterInput('employeeRole',false);
            $readOnly = filterInput('readOnly',false);
            $projectId = filterInput('projectId',false);
            $employeeId = filterInput('employeeId',false);
            $assessorId = filterInput('assessorId',false);
            $tasks = $this->getTaskList($employeeId,$projectId);
            if($readOnly){
                foreach($tasks as $task){
                    $taskId = $task['id'];
                    // Get the performance for each task
                    $sql1 = "SELECT * FROM performance WHERE task_id=$taskId 
                    AND assessor_id IN 
                    (SELECT id FROM employee WHERE employee_role_id IN 
                    (SELECT id FROM employee_role WHERE role=1))";
                    $result1 = $this->con->query($sql1);
                    if($result1->num_rows == 1){
                        $assessment = $result1->fetch_assoc();
                        $assessments[] = [
                            'isAssessed'=> true,
                            'taskId'=> $taskId,
                            'description'=>$task['description'],
                            'performanceId'=> $assessment['id'],
                            'comments'=>$assessment['comments'],
                            'rating'=>$assessment['rating'],
                            'assessorId'=>$assessorId,
                            'employeeId'=>$employeeId,
                            'projectId'=>$projectId,
                            'readOnly'=>$readOnly ? true : false,
                        ];
                    }
                    else{
                        $assessments[] = [
                            'isAssessed'=> false,
                            'taskId'=> $taskId,
                            'description'=>$task['description'],
                            'assessorId'=>$assessorId,
                            'employeeId'=>$employeeId,
                            'projectId'=>$projectId,
                            'readOnly'=>$readOnly ? true : false,
                        ];
                    }
                }
                // echo json_encode(['assessments'=>$tasks]);
                echo json_encode([
                    'status'=>"SUCCESS",
                    'assessments'=>$assessments,
                ]);
            }
            else{
                foreach($tasks as $task){
                    $taskId = $task['id'];
                    // Get the performance for each task
                    $sql1 = "SELECT * FROM performance WHERE task_id=$taskId AND assessor_id=$assessorId";
                    $result1 = $this->con->query($sql1);
                    if($result1->num_rows == 1){
                        $assessment = $result1->fetch_assoc();
                        $assessments[] = [
                            'isAssessed'=> true,
                            'taskId'=> $taskId,
                            'description'=>$task['description'],
                            'performanceId'=> $assessment['id'],
                            'comments'=>$assessment['comments'],
                            'rating'=>$assessment['rating'],
                            'assessorId'=>$assessorId,
                            'employeeId'=>$employeeId,
                            'projectId'=>$projectId,
                            'readOnly'=>$readOnly ? true : false,
                        ];
                    }
                    else{
                        $assessments[] = [
                            'isAssessed'=> false,
                            'taskId'=> $taskId,
                            'description'=>$task['description'],
                            'assessorId'=>$assessorId,
                            'employeeId'=>$employeeId,
                            'projectId'=>$projectId,
                            'readOnly'=>$readOnly ? true : false,
                        ];
                    }
                }
                echo json_encode([
                    'status'=>"SUCCESS",
                    'assessments'=>$assessments,
                ]);
                
            }
        }

        function saveAssessment(){
            echo json_encode("Thank you Lord Jesus.");
        }
    }

?>