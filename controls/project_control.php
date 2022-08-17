<?php

    require_once '../misc/utils.php';
    class ProjectControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
            $this->tableName = 'project';
        }

        function getProjectsList($q=null,$columns=null){
            $projects = [];
            if($q){
                $spreadColumns = spreadSearchColumns($columns,$q);
                $sql1 = "SELECT * FROM $this->tableName WHERE $spreadColumns";
            }
            else{
                $sql1 = "SELECT * FROM $this->tableName";
            }
            $results1 = $this->con->query($sql1);
            if($results1->num_rows > 0){
                while($row = $results1->fetch_assoc()){
                    $projects[] = $row;
                }
            }
            return $projects;
        }

        function getProject($projectId){
            // $sql1 = "SELECT *,COUNT(`assign`.`project_id`) AS employeesAssigned 
            // FROM project,assign WHERE id=$projectId";
            $sql1 = "SELECT * FROM project WHERE id=$projectId";
            $result1 = $this->con->query($sql1);
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
            $sql2 = "SELECT COUNT(project_id) AS employeesAssigned FROM assign WHERE project_id=$projectId";
            $result2 = $this->con->query($sql2);
            $generalItems['employeesAssigned'] = $result2->fetch_assoc()['employeesAssigned'];

            $sql3 = "SELECT COUNT(project_id) AS tasks FROM task WHERE project_id=$projectId";
            $result3 = $this->con->query($sql3);
            $generalItems['tasks'] = $result3->fetch_assoc()['tasks'];

            // Get total progress
            $totalEmployeeProgress = [];
            $sql4 = "SELECT IFNULL(rating,0) as rating FROM `performance` INNER JOIN task
            ON `task`.`id`=`performance`.`task_id`
            WHERE `task`.`project_id`=$projectId;";
            $results4 = $this->con->query($sql4);

            // Get employee progress
            while($row4 = $results4->fetch_assoc()){
                $totalEmployeeProgress[] = $row4['rating'];
            }
            $generalItems['totalProgress'] = round(array_sum($totalEmployeeProgress) / count($totalEmployeeProgress),2);

            return $generalItems;
        }


        function generateEmployeeProgressItems($projectId){
            $employeeProgressHtml = "";
            // Get all employees in the project
            $sql1 = "SELECT employee_id FROM assign WHERE project_id=$projectId";
            $results1 = $this->con->query($sql1);
            while($row1 = $results1->fetch_assoc()){
                $employeeId = $row1['employee_id'];
                $employeeProgress = $this->employeeProgress($employeeId,$projectId);
                // Get employee details
                $sql2 = "SELECT CONCAT_WS(' ',`employee`.`surname`,`employee`.`other_names`) as name,
                `department`.`name` AS department
                FROM employee
                INNER JOIN department
                ON `employee`.`department_id`=`department`.`id` AND `employee`.`id`=$employeeId";
                $results2 = $this->con->query($sql2);
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
            // Get all employees 
            $sql1 = "SELECT employee_id FROM assign WHERE project_id=$projectId";
            $results1 = $this->con->query($sql1);
            while($row1 = $results1->fetch_assoc()){
                $employeeId = $row1['employee_id'];
                $employeeProgress = $this->employeeProgress($employeeId,$projectId);
                // FIXME: Error in generating department progress
                // Get all tasks and their ratings for an employee
                // $employeeProgress = 0;
                // $sql3 = "SELECT IFNULL(`performance`.`rating`,0) as rating FROM performance
                // INNER JOIN task
                // ON `task`.`id`=`performance`.`task_id`
                // WHERE `task`.`project_id`=$projectId AND `task`.`employee_id` = $employeeId";
                // $results3 = $this->con->query($sql1);
                // if($results3->num_rows > 0){
                //     while($row3 = $results1->fetch_assoc()){
                //         $$employeeProgress += $row3['rating'];
                //     }
                // }

                // 
                $employeeTasks = $this->employeeTasks($employeeId,$projectId);
                // Get department details
                $sql2 = "SELECT `department`.`name` AS department, `department`.`id` AS id FROM employee 
                INNER JOIN department
                ON `employee`.`department_id`=`department`.`id`
                WHERE  `employee`.`id`=$employeeId";
                $results2 = $this->con->query($sql2);
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
                $progress = round(array_sum($departmentData['progress']) /count($departmentData['progress']),2);
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
            WHERE `task`.`project_id`=$projectId AND `task`.`employee_id` = $employeeId";
            $results1 = $this->con->query($sql1);
            if($results1->num_rows > 0){
                while($row1 = $results1->fetch_assoc()){
                    $ratings += $row1['rating'];
                }
                return round($ratings / $results1->num_rows,2);
            }
            return $ratings;
        }


        function employeeTasks($employeeId,$projectId){
            $sql1 = "SELECT COUNT(*) as tasks FROM task WHERE employee_id=$employeeId AND project_id=$projectId";
            $results1 = $this->con->query($sql1);
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
                    $colorCodeForProgress = colorCodesForProgress($totalProgress);
                    
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
                                <h1 class='$colorCodeForProgress display-4'>$totalProgress%</h1>
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
                $sql2 = "SELECT id,CONCAT_WS(' ',surname,other_names) as name FROM employee WHERE employee_role_id=$employeeRoleId";
                
                // No employees
                $employeesHtml = "<p class='text-center text-muted lead'>No Employee Found.</p>";
                $results2 = $this->con->query($sql2);
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
            $sql1 = "SELECT id FROM employee_role WHERE name='$employeeRole'";
            $results1 = $this->con->query($sql1);
            if($results1->num_rows == 1){
                $employeeRoleId = $results1->fetch_assoc()['id'];
                $employees = [];
                // Get employees
                if($q){
                    $searchColumns = spreadSearchColumns(['surname','other_names'],$q);
                    $sql2 = "SELECT id, CONCAT_WS(' ',surname,other_names) AS name FROM employee WHERE employee_role_id=$employeeRoleId AND $searchColumns";
                }
                else{
                    $sql2 = "SELECT id, CONCAT_WS(' ',surname,other_names) AS name FROM employee WHERE employee_role_id=$employeeRoleId";
                }
                $results2 = $this->con->query($sql2);
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
            $assignedEmployees = explode(',',filterInput('assignedEmployees'));
            $todaysDate = date('Y-m-d');
            // Insert the project and get it's
            $sql1 = "INSERT INTO project(name,date_created,deadline,is_open)
            VALUE('$name','$todaysDate','$deadline',$isOpen)";
            if($this->con->query($sql1)){
                // Get the latest project is
                $sql2 = "SELECT id FROM project ORDER BY id DESC LIMIT 1";
                $results2 = $this->con->query($sql2);
                if($results2->num_rows == 1){
                    $projectId = $results2->fetch_assoc()['id'];
                    $errorAssigning = false;
                    // Assign employees to project
                    foreach($assignedEmployees as $employee){
                        $sql3 = "INSERT INTO assign(project_id,employee_id) VALUE($projectId,$employee)";
                        if(!$this->con->query($sql3)){
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
            $sql1 = "DELETE FROM project WHERE id=$projectId";
            if($this->con->query($sql1)){
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
                $sql1 = "UPDATE project SET assess=$value WHERE id=$projectId";
            }
            else{
                $sql1 = "UPDATE project SET is_open=$value WHERE id=$projectId";
            }

            if($this->con->query($sql1)){
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
            if($employeeRole == 1 or $employeeRole == 0){
                if($q){
                    $spreadColumns = spreadSearchColumns($columns,$q);
                    $sql1 = "SELECT * FROM project
                    WHERE $spreadColumns";
                }
                else{
                    $sq1 = "SELECT * FROM project";
                }
            }
            // if($employeeRole == 2){

            // }
            // Staff
            else{
                if($q){
                    $spreadColumns = spreadSearchColumns($columns,$q);
                    $sql1 = "SELECT * FROM project 
                    INNER JOIN assign
                    ON `project`.`id`=`assign`.`project_id` AND `assign`.`employee_id`=$employeeId
                    WHERE $spreadColumns";
                }
                else{
                    $sq1 = "SELECT * FROM project 
                    INNER JOIN assign
                    ON `project`.`id`=`assign`.`project_id` AND `assign`.`employee_id`=$employeeId";
                }
            }
            $results1 = $this->con->query($sq1);
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
                        $progress = $generalItems['totalProgress'];
                    }
                    else{
                        $tasks = $this->employeeTasks($employeeId,$projectId);
                        $progress = $this->employeeProgress($employeeId,$projectId);
                        $currentDate = new DateTime();
                        $deadline = new DateTime($row1['deadline']);
                        $daysLeft = $deadline->diff($currentDate)->format('%a');
                    }
                    $projects[] = $row1;
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
?>
