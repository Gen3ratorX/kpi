<?php
    require_once '../misc/utils.php';

    class EmployeeControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
            $this->tableName = 'employee';
        }

        function getEmployeesList($q=null, $columns=null){
            $employees = [];
            // $sql1 = "SELECT * FROM $this->tableName";
            if($q){
                $spreadColumns = spreadSearchColumns($columns,$q);
                $sql1 = "SELECT * FROM $this->tableName WHERE $spreadColumns";
            }
            else{
                $sql1 = "SELECT * FROM $this->tableName";
            }

            $results = $this->con->query($sql1);
            while($row1 = $results->fetch_assoc()){
                $employees[] = $row1;
            }
            return $employees;
        }

        function employeeAdminListTemplate(){
            $employees = $this->getEmployeesList();
            $employeesHtml = "";
            if($employees){
                $employeeHtml = "";
                foreach($employees as $employee){
                    $name = "{$employee['surname']} {$employee['other_names']}";
                    $employeeHtml .= "
                        <div class='col'>
                            <div class='card shadow employee action-item'>
                                <div class='options d-flex align-items-center justify-content-center'>
                                    <div>
                                        <button class='btn btn-md btn-2 mb-2 delete-employee-attempt' id='employee-{$employee['id']}' data-bs-toggle='modal' data-bs-target='#deleteItem'>Delete</button>
                                        <br>
                                        <a href='./employee_form.php?id={$employee['id']}' class='btn btn-md btn-4'>Edit</a>
                                    </div>
                                </div>
                                <div class='card-body'>
                                    <h4 class'text-truncate text-secondary'>$name</h4>
                                    <p class='m-0 text-secondary'>Username: <span class='text-dark'>{$employee['username']}</span></p>
                                    <p class='m-0 text-secondary'>Phone: <span class='text-dark'>{$employee['phone']}</span></p>
                                </div>
                            </div>
                        </div>
                    ";
                }

                $employeesHtml = "
                    <section id='employees' class='row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3'>
                        $employeeHtml
                    </section>
                ";
            }
            else{
                $employeesHtml .= "
                    <!-- No Item -->
                    <section class='no-item'>
                        No Employee Has Been added.
                        <div>
                            <a href='./employee_form.php' class='btn btn-1 btn-md'> Add Employee </a>
                        </div>
                    </section>
                ";
            }
            return $employeesHtml;
        }

        function generateRolesValues($roles,$select = 0){
            $options = "";
            foreach($roles as $role){
                $select == $role['id']
                    ? $options .= "
                        <option selected value='{$role['id']}'>{$role['name']}</option>
                    "
                    : $options .= "
                        <option value='{$role['id']}'>{$role['name']}</option>
                    ";
            }
            return $options;
        }

        function generateUnitsValues($units){
            $options = "";
            return $options;
        }

        function generateDepartmentsValues($departments){
            $options = "";
            return $options;
        }

        function generateUsername($surname,$otherNames){
            $username = '';
            $otherNamesLst = explode(' ',$otherNames);
            foreach($otherNamesLst as $otherName){
                $username .= strtolower($otherName[0]);
            }
            $username .= strtolower($surname);

            // Check if it exists in the database
            $sql1 = "SELECT * FROM $this->tableName WHERE username='$username'";
            $results1 = $this->con->query($sql1);
            $numRows = $results1->num_rows + 1;
            $username .= "$numRows";
            return $username;
        }

        
        function getEmployee($employeeId){
            $sql1 = "SELECT * FROM $this->tableName WHERE id=$employeeId";
            $result1 = $this->con->query($sql1);
            return $result1->fetch_assoc();
        }
        
        function saveEmployee(){
            $surname = filterInput('surname');
            $otherNames = filterInput('otherNames');
            $phone = filterInput('phone');
            $email = filterInput('email');
            $location = filterInput('location');
            $role = filterInput('role');
            $unit = filterInput('unit');
            $department = filterInput('department');
            $username = $this->generateUsername($surname,$otherNames);
            $password = password_hash($username,PASSWORD_BCRYPT);

            $sql1 = "INSERT INTO 
            $this->tableName(surname,other_names,phone,email,employee_role_id,location,username,password)
            VALUE('$surname','$otherNames','$phone','$email',$role,'$location','$username','$password')";
            if($department and $unit){
                $sql1 = "INSERT INTO 
                $this->tableName(surname,other_names,phone,email,employee_role_id,location,username,password,department_id,unit_id)
                VALUE('$surname','$otherNames','$phone','$email',$role,'$location','$username','$password',$department,$unit)";
            }
            elseif($department and !$unit){
                $sql1 = "INSERT INTO 
                $this->tableName(surname,other_names,phone,email,employee_role_id,location,username,password,department_id)
                VALUE('$surname','$otherNames','$phone','$email',$role,'$location','$username','$password',$department)";
            }

            if($this->con->query($sql1)){
                http_response_code(201);
                echo json_encode(['status'=>'SUCCESS']);
            }else{
                http_response_code(500);
                echo json_encode(['status'=>'ERROR','sql'=>$sql1]);
            }
        }

        function editEmployee(){
            $employeeId = filterInput('employeeId');
            $surname = filterInput('surname');
            $otherNames = filterInput('otherNames');
            $phone = filterInput('phone');
            $email = filterInput('email');
            $location = filterInput('location');
            $role = filterInput('role');
            $unit = filterInput('unit');
            $department = filterInput('department');

            $sql1 = "UPDATE $this->tableName 
            SET surname='$surname', other_names='$otherNames', phone='$phone', email='$email', location='$location', employee_role_id=$role WHERE id=$employeeId";
            if($department and $unit){
                $sql1 = "UPDATE $this->tableName 
                SET surname='$surname', other_names='$otherNames', phone='$phone', email='$email', location='$location', employee_role_id=$role, 
                department_id=$department, unit_id=$unit WHERE id=$employeeId";
            }
            elseif($department and !$unit){
                $sql1 = "UPDATE $this->tableName 
                SET surname='$surname', other_names='$otherNames', phone='$phone', email='$email', location='$location', employee_role_id=$role, 
                department_id=$department WHERE id=$employeeId";
            }

            if($this->con->query($sql1)){
                http_response_code(201);
                echo json_encode(['status'=>'SUCCESS']);
            }else{
                http_response_code(500);
                echo json_encode(['status'=>'ERROR','sql'=>$sql1]);
            }

        }

        function deleteEmployee(){
            $employeeId = filterInput('employeeId');
            $sql1 = "DELETE FROM $this->tableName WHERE id='$employeeId'";
            if($this->con->query($sql1)){
                http_response_code(204);
                echo json_encode([
                    'status'=> "SUCCESS"
                ]);
            }
            else{
                http_response_code(500);
                echo json_encode([
                    'status'=>'SUCCESS'
                ]);
            }
        }
    }
?>