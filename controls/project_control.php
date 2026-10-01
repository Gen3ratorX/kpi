<?php

    require_once '../misc/utils.php';
    class ProjectControl{
        private $con;
        private $tableName;

        function __construct($con)
        {
            $this->con = $con;
            $this->tableName = 'project';
        }

        function getProjectsList($q=null,$columns=null){
            $projects = [];
            $params = [];
            if($q){
                [$condition, $params] = searchCondition($columns,$q);
                $sql1 = "SELECT * FROM $this->tableName WHERE $condition";
            }
            else{
                $sql1 = "SELECT * FROM $this->tableName";
            }
            $results1 = $this->con->execute_query($sql1, $params);
            if($results1->num_rows > 0){
                while($row = $results1->fetch_assoc()){
                    $projects[] = $row;
                }
            }
            return $projects;
        }

        function getProject($projectId){
            $result1 = $this->con->execute_query("SELECT * FROM project WHERE id=?", [$projectId]);
            if($result1->num_rows == 1){
                return $result1->fetch_assoc();
            }
        }

        function generalDashboardItems($projectId){
            $generalItems = [];
            // Get project details
            $project = $this->getProject($projectId);
            $generalItems['projectName'] = $project['name'];
            $currentDate = new DateTime();
            $deadline = new DateTime($project['deadline']);
            $generalItems['daysLeft'] = $deadline->diff($currentDate)->format('%a');

            // Get task and employee details
            $result2 = $this->con->execute_query("SELECT COUNT(project_id) AS employeesAssigned FROM assign WHERE project_id=?", [$projectId]);
            $generalItems['employeesAssigned'] = $result2->fetch_assoc()['employeesAssigned'];

            $result3 = $this->con->execute_query("SELECT COUNT(project_id) AS tasks FROM task WHERE project_id=?", [$projectId]);
            $generalItems['tasks'] = $result3->fetch_assoc()['tasks'];

            // Get total progress
            $totalEmployeeProgress = [];
            $sql4 = "SELECT IFNULL(rating,0) as rating FROM `performance` INNER JOIN task
            ON `task`.`id`=`performance`.`task_id`
            WHERE `task`.`project_id`=?";
            $results4 = $this->con->execute_query($sql4, [$projectId]);

            // Get employee progress
            while($row4 = $results4->fetch_assoc()){
                $totalEmployeeProgress[] = $row4['rating'];
            }
            $projectTarget = $project['target'];
            $generalItems['totalProgress'] = count($totalEmployeeProgress) > 0
            ? round(
                (
                    array_sum($totalEmployeeProgress) / count($totalEmployeeProgress) * $projectTarget
                ) / 100,2
            ) 
            : 0;

            return $generalItems;
        }


        function generateEmployeeProgressItems($projectId){
            $employeeProgressHtml = "";
            // Get all employees in the project
            $results1 = $this->con->execute_query("SELECT employee_id FROM assign WHERE project_id=?", [$projectId]);
            while($row1 = $results1->fetch_assoc()){
                $employeeId = $row1['employee_id'];
                $employeeProgress = $this->employeeProgress($employeeId,$projectId);
                // Get employee details
                $sql2 = "SELECT CONCAT_WS(' ',`employee`.`surname`,`employee`.`other_names`) as name,
                `department`.`name` AS department
                FROM employee
                INNER JOIN department
                ON `employee`.`department_id`=`department`.`id` AND `employee`.`id`=?";
                $results2 = $this->con->execute_query($sql2, [$employeeId]);
                $row2 = $results2->fetch_assoc();
                $employeeName = $row2['name'];
                $department = $row2['department'];
                $colorCodeForProgress = colorCodesForProgress($employeeProgress);

                // Get number of tasks
                $tasks = $this->employeeTasks($employeeId,$projectId);

                // Generate html code
                $employeeProgressHtml .= "
                    <div class='card item shadow-sm department-progress-item mb-3'>
                        <div class='card-body d-flex justify-content-between align-items-center'>
                            <section class='details text-start'>
                                $employeeName
                                <div class='d-flex flex-sm-row flex-column'>
                                    <p class='project-item small'>
                                        Tasks: <span>$tasks</span>
                                    </p>
                                    <p class='project-item small'>
                                        Department: <span>$department</span>
                                    </p>
                                </div>
                            </section>
                            <section class='percentage $colorCodeForProgress text-danger'>
                                $employeeProgress%
                            </section>
                        </div>
                    </div>
                ";
            }

            return $employeeProgressHtml;
        }

        function generateDepartmentProgressItems($projectId){
            $departmentsData = [];
            $departmentProgressHtml = "";
            $noOfAssessments = 0;
            // Get all employees 
            $results1 = $this->con->execute_query("SELECT employee_id FROM assign WHERE project_id=?", [$projectId]);
            while($row1 = $results1->fetch_assoc()){
                $employeeId = $row1['employee_id'];
                // Get all tasks and their ratings for an employee
                $employeeProgress = 0;
                $sql3 = "SELECT IFNULL(`performance`.`rating`,0) as rating FROM performance
                INNER JOIN task
                ON `task`.`id`=`performance`.`task_id`
                WHERE `task`.`project_id`=? AND `task`.`employee_id` = ?";
                $results3 = $this->con->execute_query($sql3, [$projectId, $employeeId]);
                if($results3->num_rows > 0){
                    while($row3 = $results3->fetch_assoc()){
                        $employeeProgress += $row3['rating'];
                        $noOfAssessments++;
                    }
                }

                // 
                $employeeTasks = $this->employeeTasks($employeeId,$projectId);
                // Get department details
                $sql2 = "SELECT `department`.`name` AS department, `department`.`id` AS id FROM employee 
                INNER JOIN department
                ON `employee`.`department_id`=`department`.`id`
                WHERE  `employee`.`id`=?";
                $results2 = $this->con->execute_query($sql2, [$employeeId]);
                $row2 = $results2->fetch_assoc();
                $departmentId = $row2['id'];
                $department = $row2['department'];
                // Department has been added
                if(array_key_exists($departmentId,$departmentsData)){
                    $departmentsData[$departmentId]['progress'][] = $employeeProgress;
                    $departmentsData[$departmentId]['tasks'][] = $employeeTasks;
                    $departmentsData[$departmentId]['employeesAssigned']++;

                }
                // Department is not added
                else{
                    $departmentsData[$departmentId] = [
                        'tasks'=>[$employeeTasks],
                        'progress'=>[$employeeProgress],
                        'name'=>$department,
                        'employeesAssigned'=>1,
                    ];
                }
            }
            // Go through department data
            foreach($departmentsData as $departmentData){
                $tasks = array_sum($departmentData['tasks']);
                $progress = $noOfAssessments > 0 
                ? round(array_sum($departmentData['progress']) / $noOfAssessments,2)
                : 0;
                $employeesAssigned = $departmentData['employeesAssigned'];
                $departmentName = $departmentData['name'];
                $colorCodeForProgress = colorCodesForProgress($progress);

                $departmentProgressHtml .= "
                    <div class='card item shadow-sm department-progress-item mb-3'>
                        <div class='card-body d-flex justify-content-between align-items-center'>
                            <section class='details text-start'>
                                $departmentName
                                <div class='d-flex flex-sm-row flex-column'>
                                    <p class='project-item small'>
                                        Tasks: <span>$tasks</span>
                                    </p>
                                    <p class='project-item small'>
                                        Employees Assigned: <span>$employeesAssigned</span>
                                    </p>
                                </div>
                            </section>
                            <section class='percentage $colorCodeForProgress'>
                                $progress%
                            </section>
                        </div>
                    </div>
                ";
            }

            return $departmentProgressHtml;
        }

        function employeeProgress($employeeId,$projectId){
            $ratings = 0;
            // Get all tasks and their ratings
            $sql1 = "SELECT IFNULL(`performance`.`rating`,0) as rating FROM performance
            INNER JOIN task
            ON `task`.`id`=`performance`.`task_id`
            WHERE `task`.`project_id`=? AND `task`.`employee_id` = ?";
            $results1 = $this->con->execute_query($sql1, [$projectId, $employeeId]);
            if($results1->num_rows > 0){
                while($row1 = $results1->fetch_assoc()){
                    $ratings += $row1['rating'];
                }
                return round($ratings / $results1->num_rows,2);
            }
            return $ratings;
        }


        function employeeTasks($employeeId,$projectId){
            $results1 = $this->con->execute_query("SELECT COUNT(*) as tasks FROM task WHERE employee_id=? AND project_id=?", [$employeeId, $projectId]);
            return $results1->fetch_assoc()['tasks'];

        }


        function projectAdminListTemplate(){
            $projects = $this->getProjectsList();
            $projectsHtml = "";
            if($projects){
                $projectHtml = "";
                foreach($projects as $project){
                    $projectId = $project['id'];
                    $generalItems = $this->generalDashboardItems($projectId);
                    $totalProgress = $generalItems['totalProgress'];
                    $idealTarget = $project['target'];
                    // $colorCodeForProgress = colorCodesForProgress($totalProgress);
                    
                    $projectHtml .= "
                        <div class='card shadow-sm action-item mb-3'>
                            <div class='options d-flex align-items-center justify-content-center'>
                                <div>
                                    <button class='btn btn-md btn-2 me-2 delete-project-attempt' id='project-{$project['id']}' data-bs-toggle='modal' data-bs-target='#deleteItem'>Delete</button>
                                    <!-- <a href='' class='btn btn-md btn-4 me-2'>Edit</a> -->
                                    <a href='./project_detail.php?id={$project['id']}' class='btn btn-md btn-3'>View</a>
                                </div>
                            </div>
                            <div class='card-body d-flex justify-content-between align-items-center'>
                                <div class='flex-grow-1'>
                                    <h4 class='project-name'>{$project['name']}</h4>
                                    <div class='d-flex flex-column flex-md-row'>
                                        <p class='project-item'>Tasks: <span>{$generalItems['tasks']}</span></p>
                                        <p class='project-item'>Employees Assigned: <span>{$generalItems['employeesAssigned']}</span></p>
                                        <p class='project-item'>Days Left: <span>{$generalItems['daysLeft']}</span></p>
                                    </div>
                                </div>
                                <div>
                                    <section>
                                        <p class='m-0 text-center'>Ideal Target</p>
                                        <h1 class='text-primary display-6 text-center'>$idealTarget%</h1>
                                    </section>
                                    <section>
                                        <p class='m-0 text-center'>Achieved Target</p>
                                        <h1 class='text-warning display-6 text-center'>$totalProgress%</h1>
                                    </section>
                                </div>
                            </div>
                        </div>
                    ";
                }

                $projectsHtml = "
                    <section id='projects'>
                        $projectHtml
                    </section>
                ";
            }
            else{
                $projectsHtml .= "
                    <!-- No Item -->
                    <section class='no-item'>
                        No Project Has Been added.
                        <div>
                            <a href='project_form.php' class='btn btn-1 btn-md'> Add Project </a>
                        </div>
                    </section>
                ";
            }
            return $projectsHtml;
        }

        function generateAssignEmployeesHtml(){
            $assignEmployeesHtml = "";
            // Get all roles apart from auditors
            $roles = [];
            $sql1 = "SELECT * FROM employee_role WHERE role NOT IN  (0,1)";
            $results1 = $this->con->query($sql1);
            while($row1 = $results1->fetch_assoc()){
                $roles[] = $row1;
            }
            
            $tabItemsHtml = "";
            $tabContentHtml = "";
            for($index = 0; $index < count($roles); $index++){
                $roleName = $roles[$index]['name'];
                $khebabCaseRoleName = convertToKhebabCase($roleName,' ');
                $employeeRoleId = $roles[$index]['id'];
                $ariaSelected = $index == 0 ? 'true' : 'false';
                $tabItemActive = $index == 0 ? 'active' : '';
                $tabContentActive = $index == 0 ? 'show active' : '';
                // Form tab items
                $tabItemsHtml .= "
                    <li class='nav-item' role='presentation'>
                        <button class='nav-link $tabItemActive' id='nav-$khebabCaseRoleName-tab' data-bs-toggle='tab' data-bs-target='#nav-$khebabCaseRoleName' type='button' role='tab' aria-controls='nav-$khebabCaseRoleName' aria-selected='$ariaSelected'>$roleName</button>
                    </li>
                ";
                // Get employees with that role
                $sql2 = "SELECT id,CONCAT_WS(' ',surname,other_names) as name FROM employee WHERE employee_role_id=? AND status='active'";
                
                // No employees
                $employeesHtml = "<p class='text-center text-muted lead'>No Employee Found.</p>";
                $results2 = $this->con->execute_query($sql2, [$employeeRoleId]);
                // Employees 
                if($results2->num_rows > 0){
                    $employeesHtml = "";
                    while($row2 = $results2->fetch_assoc()){
                        $employeeName = $row2['name'];
                        $employeeId = $row2['id'];
                        $employeesHtml .= "
                            <p class='employee-item' data-employee-id='$employeeId'>$employeeName</p>
                        ";
                    }
                }

                $tabContentHtml .= "
                    <div class='tab-pane fade $tabContentActive' id='nav-$khebabCaseRoleName' role='tabpanel' aria-labelledby='nav-$khebabCaseRoleName-tab' tabindex='$index'>
                        <!-- Search -->
                        <section class='my-3'>
                            <div class='row g-2 search'>
                                <div class='col'>
                                    <input placeholder='Search for employees..' type='search' class='form-control search-project-employee-input'>
                                </div>
                                <div class='col-auto'>
                                    <button data-employee-role='$roleName' type='button' class='btn btn-3 btn-sm search-project-employee'>Search</button>
                                </div>
                            </div>
                        </section>
                        <!-- Employeees -->
                        <section class='project-employees'>
                            $employeesHtml
                        </section>
                    </div>
                ";
            }

            $assignEmployeesHtml = "
                <ul class='nav nav-tabs mb-3' id='nav-tab' role='tablist'>
                    $tabItemsHtml
                </ul>
                <div class='tab-content mx-2' id='nav-tabContent'>
                    $tabContentHtml
                </div>

            ";
            return $assignEmployeesHtml;

        }

        function searchProjectEmployees(){
            $employeeRole = filterInput('employeeRole',false);
            $q = filterInput('q',false);
            // Get employee role id
            $results1 = $this->con->execute_query("SELECT id FROM employee_role WHERE name=?", [$employeeRole]);
            if($results1->num_rows == 1){
                $employeeRoleId = $results1->fetch_assoc()['id'];
                $employees = [];
                // Get employees
                $sql2 = "SELECT id, CONCAT_WS(' ',surname,other_names) AS name FROM employee WHERE employee_role_id=? AND status='active'";
                $params = [$employeeRoleId];
                if($q){
                    [$condition, $searchParams] = searchCondition(['surname','other_names'],$q);
                    $sql2 .= " AND $condition";
                    $params = array_merge($params, $searchParams);
                }
                $results2 = $this->con->execute_query($sql2, $params);
                while($row2 = $results2->fetch_assoc()){
                    $employees[] = $row2;
                }
                echo json_encode(['status'=>'SUCCESS','employees'=>$employees]);
            }
            else{
                http_response_code(500);
                echo json_encode(['status'=>'ERROR']);
            }
            


        }

        function saveProject(){
            $name = filterInput('projectName');
            $deadline = filterInput('deadline');
            $isOpen = filterInput('isOpen');
            $idealTarget = filterInput('target');
            $assignedEmployees = explode(',',filterInput('assignedEmployees'));
            $todaysDate = date('Y-m-d');
            // Insert the project and get it's
            $sql1 = "INSERT INTO project(name,date_created,deadline,is_open,target) VALUE(?,?,?,?,?)";
            if($this->con->execute_query($sql1, [$name, $todaysDate, $deadline, $isOpen, $idealTarget])){
                // The id of the project just inserted (not "the newest row", which another save could change)
                $projectId = $this->con->insert_id;
                if($projectId){
                    $errorAssigning = false;
                    // Assign employees to project
                    foreach(array_filter($assignedEmployees, 'strlen') as $employee){
                        if(!$this->con->execute_query("INSERT INTO assign(project_id,employee_id) VALUE(?,?)", [$projectId, $employee])){
                            $errorAssigning = true; 
                        }
                    }

                    if($errorAssigning){
                        http_response_code(500);
                        echo json_encode(['status'=>'ERROR','Did not assign']); 
                    }
                    else{
                        http_response_code(201);
                        echo json_encode(['status'=>'SUCCESS']); 
                    }
                }
                else{
                    http_response_code(500);
                    echo json_encode(['status'=>'ERROR','Project Id Not Found.']);  
                }
            }
            else{
                http_response_code(500);
                echo json_encode(['status'=>'ERROR']);
            }
        }

        function deleteProject(){
            $projectId = filterInput('projectId');
            if($this->con->execute_query("DELETE FROM project WHERE id=?", [$projectId])){
                http_response_code(204);
                echo json_encode(['status'=>"SUCCESS"]);
            }
            else{
                http_response_code(500);
                echo json_encode(['status'=>"ERROR"]);
            }
        }

        function updateProjectOptions(){
            $option = filterInput('option');
            $projectId = filterInput('projectId');
            $value = filterInput('value') == 'yes'? 1: 0;
            if($option == 'assessment'){
                $sql1 = "UPDATE project SET assess=? WHERE id=?";
            }
            else{
                $sql1 = "UPDATE project SET is_open=? WHERE id=?";
            }

            if($this->con->execute_query($sql1, [$value, $projectId])){
                echo json_encode(['status'=>"SUCCESS"]);
            }
            else{
                http_response_code(500);
                echo json_encode(['status'=>"ERROR"]);
            }
        }


        // User
        function generateEmployeeProjectList($employeeId,$employeeRole,$q=null,$columns=null){
            $projects = [];
            $employeeProjectsHtml = "";
            // TODO: Special roles
            // Auditor or General Manager
            $params = [];
            $condition = '';
            if($q){
                [$condition, $params] = searchCondition($columns,$q);
            }
            if($employeeRole == 1 or $employeeRole == 0){
                $sql1 = "SELECT * FROM project" . ($q ? " WHERE $condition" : "");
            }
            // if($employeeRole == 2){

            // }
            // Staff
            else{
                $sql1 = "SELECT * FROM project 
                    INNER JOIN assign
                    ON `project`.`id`=`assign`.`project_id` AND `assign`.`employee_id`=?" . ($q ? " WHERE $condition" : "");
                $params = array_merge([$employeeId], $params);
            }
            $results1 = $this->con->execute_query($sql1, $params);
            if($results1->num_rows > 0){
                while($row1 = $results1->fetch_assoc()){
                    $projectId = $row1['id'];
                    $projectName = $row1['name'];
                    $generalItems = $this->generalDashboardItems($projectId);
                    // TODO: Special roles
                    // Auditor or General Manager
                    if($employeeRole == 1 or $employeeRole == 0){
                        $tasks = $generalItems['tasks'];
                        $daysLeft = $generalItems['daysLeft'];
                        $totalProgress = $generalItems['totalProgress'];
                        $idealTarget = $row1['target'];
                        $employeesAssigned = $generalItems['employeesAssigned'];

                        $employeeProjectsHtml .= "
                            <a href='./project_detail.php?id=$projectId'>
                                <div class='card shadow-sm action-item mb-3'>
                                    <div class='card-body d-flex justify-content-between align-items-center'>
                                        <div class='flex-grow-1'>
                                            <h4 class='project-name'>{$projectName}</h4>
                                            <div class='d-flex flex-column flex-md-row'>
                                                <p class='project-item'>Tasks: <span>{$tasks}</span></p>
                                                <p class='project-item'>Employees Assigned: <span>{$employeesAssigned}</span></p>
                                                <p class='project-item'>Days Left: <span>{$daysLeft}</span></p>
                                            </div>
                                        </div>
                                        <div>
                                            <section>
                                                <p class='m-0 text-center text-dark'>Ideal Target</p>
                                                <h1 class='text-primary display-6 text-center'>$idealTarget%</h1>
                                            </section>
                                            <section>
                                                <p class='m-0 text-center text-dark'>Achieved Target</p>
                                                <h1 class='text-warning display-6 text-center'>$totalProgress%</h1>
                                            </section>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        ";
                    }
                    else{
                        $tasks = $this->employeeTasks($employeeId,$projectId);
                        $progress = $this->employeeProgress($employeeId,$projectId);
                        $currentDate = new DateTime();
                        $deadline = new DateTime($row1['deadline']);
                        $daysLeft = $deadline->diff($currentDate)->format('%a');
                        $colorCodeForProgress = colorCodesForProgress($progress);
                        $employeeProjectsHtml .= "
                            <a href='./project_detail.php?id=$projectId'>
                                <div class='card shadow-sm item mb-3'>
                                    <div class='card-body d-flex justify-content-between align-items-center'>
                                        <div class='flex-grow-1'>
                                            <h4 class='project-name'>$projectName</h4>
                                            <div class='d-flex flex-column flex-md-row'>
                                                <p class='project-item'>Tasks: <span>$tasks</span></p>
                                                <p class='project-item'>Days Left: <span>$daysLeft</span></p>
                                            </div>
                                        </div>
                                        <h1 class='text-success $colorCodeForProgress display-4'>$progress%</h1>
                                    </div>
                                </div>
                            </a>
                        ";
                    }
                    $projects[] = $row1;
                }
            }
            else{
                $employeeProjectsHtml .= "
                    <div class='no-item'>
                        You haven't been assigned to any project yet....
                    </div>
                ";
            }
            return $employeeProjectsHtml;
        }
    }

    // ===== AJAX REQUEST HANDLER =====
    // Check if this file is being called directly for AJAX
    if (basename($_SERVER['SCRIPT_FILENAME']) == 'project_control.php' && isset($_POST['task'])) {
        // Only logged-in admins may call this handler
        require_once __DIR__ . '/../misc/admin_login_required.php';
        
        header('Content-Type: application/json');
        
        // Get database connection
        require_once '../misc/database_auth.php';
        
        // Create instance of ProjectControl
        $projectControl = new ProjectControl($con);
        
        $task = isset($_POST['task']) ? $_POST['task'] : '';
        $projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
        $value = isset($_POST['value']) ? (int)$_POST['value'] : 0;
        
        // Validate inputs
        if (empty($task) || $projectId <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'ERROR', 'message' => 'Missing required parameters']);
            exit;
        }
        
        try {
            switch ($task) {
                case 'updateAssess':
                    $stmt = $con->prepare("UPDATE project SET assess = ? WHERE id = ?");
                    $result = $stmt->execute([$value, $projectId]);
                    
                    if ($result) {
                        echo json_encode(['status' => 'SUCCESS', 'message' => 'Assessment updated']);
                    } else {
                        echo json_encode(['status' => 'ERROR', 'message' => 'Failed to update assessment']);
                    }
                    break;
                    
                case 'updateIsOpen':
                    $stmt = $con->prepare("UPDATE project SET is_open = ? WHERE id = ?");
                    $result = $stmt->execute([$value, $projectId]);
                    
                    if ($result) {
                        echo json_encode(['status' => 'SUCCESS', 'message' => 'Project status updated']);
                    } else {
                        echo json_encode(['status' => 'ERROR', 'message' => 'Failed to update project status']);
                    }
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['status' => 'ERROR', 'message' => 'Invalid task']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            error_log('project_control: ' . $e->getMessage());   // details stay in the server log
            echo json_encode(['status' => 'ERROR', 'message' => 'Could not update the project']);
        }
        exit;
    }
?>