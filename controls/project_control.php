<?php

    require_once '../misc/utils.php';
    class ProjectControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
        }

        function getProjectsList(){
            $sql1 = "SELECT * FROM project";
            $projects = [];
            $results1 = $this->con->query($sql1);
            if($results1->num_rows > 0){
                while($row = $results1->fetch_assoc()){
                    $projects[] = $row;
                }
            }
            return $projects;
        }

        function projectAdminListTemplate(){
            $projects = $this->getProjectsList();
            $projectsHtml = "";
            if($projects){
                $projectHtml = "";
                foreach($projects as $project){
                    $projectId = $project['id'];
                    // Get number of employees
                    $sql1 = "SELECT COUNT(*) as employeesAssigned FROM assign WHERE project_id=$projectId";
                    $result1 = $this->con->query($sql1);
                    $employeesAssigned = $result1->fetch_assoc()['employeesAssigned'];

                    $projectHtml .= "
                        <div class='card shadow-sm action-item mb-3'>
                            <div class='options d-flex align-items-center justify-content-center'>
                                <div>
                                    <button class='btn btn-md btn-2 me-2 delete-project-attempt' id='project-{$project['id']}' data-bs-toggle='modal' data-bs-target='#deleteItem'>Delete</button>
                                    <a href='' class='btn btn-md btn-4 me-2'>Edit</a>
                                    <a href='' class='btn btn-md btn-3'>View</a>
                                </div>
                            </div>
                            <div class='card-body d-flex justify-content-between align-items-center'>
                                <div class='flex-grow-1'>
                                    <h4 class='project-name'>{$project['name']}</h4>
                                    <div class='row g-2 row-cols-1 row-cols-sm-2 mt-3'>
                                        <p class='project-item col'>Tasks: <span>30</span></p>
                                        <p class='project-item col'>Employees Assigned: <span>$employeesAssigned</span></p>
                                    </div>
                                </div>
                                <h1 class='text-success display-4'>100%</h1>
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
            $sql1 = "SELECT * FROM employee_role WHERE name <> 'Auditor'";
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
    }
?>
