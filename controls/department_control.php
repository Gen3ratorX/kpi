<?php
    require_once '../misc/utils.php';
    // require_once './employee_control.php';

    class DepartmentControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
            $this->tableName = 'department';
        }

        function getDepartmentsList($q=null,$columns=null){
            $departments = [];
            if($q){
                $spreadColumns = spreadSearchColumns($columns,$q);
                $sql1 = "SELECT * FROM $this->tableName WHERE $spreadColumns";
            }
            else{
                $sql1 = "SELECT * FROM $this->tableName";
            }
            $results1 = $this->con->query($sql1);
            while($row1 = $results1->fetch_assoc()){
                $departments[] = $row1;
            }
            return $departments;
        }

        function departmentAdminListTemplate(){
            $departments = $this->getDepartmentsList();
            $departmentsHtml = "";
            if($departments){
                $departmentHtml = "";
                foreach($departments as $department){
                    $departmentHtml .= "
                    <div class='col'>
                        <section class='card text-center h-100 shadow role h-100 text-dark py-5 px-2 action-item'>
                            <h4 class='text-secondary'>{$department['name']}</h4>
                            <div class='options d-flex align-items-center justify-content-center'>
                                <div class='text-center'>
                                    <button title='Delete Department' class='mb-2 btn btn-2 delete-department-attempt btn-md' data-bs-toggle='modal' data-bs-target='#deleteItem' id='department-{$department['id']}'>
                                        Delete
                                    </button> <br/>
                                    <a href='./department_form.php?id={$department['id']}' title='Edit Department' class='btn btn-4 btn-md' href='#'>
                                        Edit
                                    </a>
                                <div>
                            </div>
                        </section>
                    </div>
                    ";
                }

                $departmentsHtml = "
                    <section class='row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 gy-3' id='departments'>
                        $departmentHtml
                    </section>
                ";
            }
            else{
                $departmentsHtml .= "
                    <!-- No Item -->
                    <section class='no-item'>
                        No Department Has Been added.
                        <div>
                            <a href='department_form.php' class='btn btn-1 btn-md'> Add Department </a>
                        </div>
                    </section>
                ";
            }
            return $departmentsHtml;
        }

        function generateDefaultDepartmentHeadContent($edit=false,$department=null){
            if($edit and $department){
                echo "<div class='col-auto'>
                <div data-department-head-id='{$department['employee_id']}' class='department-head-options' id='selected-department-head'>{$department['departmentHeadName']}</div>
                </div>";
            }
            else{
                // Get all managers
                $sql1 = "SELECT CONCAT_WS(' ',surname,other_names) AS name,id FROM employee 
                WHERE employee_role_id IN (SELECT id FROM employee_role WHERE role=2) AND id NOT IN (SELECT IFNULL(employee_id,0) FROM department)";
                $results1 = $this->con->query($sql1);
                if($results1->num_rows > 0){
                    $returnValue = "";
                    while($row1 = $results1->fetch_assoc()){
                        $returnValue .= "
                            <div class='col-auto'>
                                <div data-department-head-id='{$row1['id']}' class='department-head-options'>{$row1['name']}</div>
                            </div>
                        ";
                    }
                    echo $returnValue;
                }
                else{
                    echo "<div class='col-12'>
                            <p class='text-center text-muted lead'>No Employee Has Been Selected.</p>
                        </div>";
                }
            }
        }

        function searchDepartmentHeads(){
            $employees = [];
            $q = filterInput('q',false);
            $columns = ['surname','other_names'];
            $speadColumns = spreadSearchColumns($columns,$q);
            $sql1 = "SELECT CONCAT_WS(' ',surname,other_names) AS name, id FROM employee 
            WHERE employee_role_id IN (SELECT id FROM employee_role WHERE role=2) 
            AND $speadColumns";
            $results1 = $this->con->query($sql1);
            while($row1 = $results1->fetch_assoc()){
                $employees[] = $row1;
            }
            echo json_encode(['employees'=>$employees]);
        }
        
        function getDepartment($departmentId){
            $sql1 = "SELECT * FROM department WHERE id=$departmentId";
            $result1 = $this->con->query($sql1);
            if($result1->num_rows == 1){
                $row1 = $result1->fetch_assoc();
                if($row1['employee_id']){
                    $employeeId = $row1['employee_id'];
                    $sql2 = "SELECT CONCAT_WS(' ',surname,other_names) AS departmentHeadName, `employee`.`id` AS departmentHeadId, name, employee_id FROM employee
                    INNER JOIN department
                    ON  `employee`.`id` = $employeeId AND `department`.`id`=$departmentId";
                    $result2 = $this->con->query($sql2);
                    $row2 = $result2->fetch_assoc();
                    return $row2;
                }
                return $row1;
            }
            return null;
        }

        function saveDepartment(){
            $departmentHeadId = filterInput('departmentHeadId');
            $department = filterInput('department');
            $sql1 = "INSERT IGNORE INTO department(name,employee_id) 
            VALUE('$department',$departmentHeadId)";
            if(!$departmentHeadId){
                $sql1 = "INSERT IGNORE INTO department(name) 
                VALUE('$department')";
            }
            if($this->con->query($sql1)){
                http_response_code(201);
                echo json_encode([
                    'status'=>'SUCCESS'
                ]);
            }
            else{
                http_response_code(500);
                echo json_encode([
                    'status'=>'ERROR'
                ]);
            }
        }

        function editDepartment(){
            $departmentHeadId = filterInput('departmentHeadId');
            $department = filterInput('department');
            $departmentId = filterInput('departmentId');
            $sql1 = "UPDATE IGNORE department SET name='$department', employee_id=$departmentHeadId WHERE id=$departmentId";
            if(!$departmentHeadId){
                $sql1 = "UPDATE IGNORE department SET name='$department', employee_id=null WHERE id=$departmentId";
            }
            if($this->con->query($sql1)){
                http_response_code(200);
                echo json_encode([
                    'status'=>'SUCCESS',
                    'sql'=> $sql1
                ]);
            }
            else{
                http_response_code(500);
                echo json_encode([
                    'status'=>'ERROR'
                ]);
            }
        }

        function deleteDepartment(){
            $departmentId = filterInput('departmentId');
            $sql1 = "DELETE FROM department WHERE id=$departmentId";
            if($this->con->query($sql1)){
                http_response_code(200);
                echo json_encode([
                    'status'=>'SUCCESS'
                ]);
            }
            else{
                http_response_code(500);
                echo json_encode([
                    'status'=>'ERROR'
                ]);
            }
        }

    }
?>