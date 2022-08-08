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
                            <div class='card shadow-sm h-100 employee action-item'>
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

        

        function generateDepartmentsValues($departments,$select = 0){
            $options = "";
            foreach($departments as $department){
                $select == $department['id']
                    ? $options .= "
                        <option selected value='{$department['id']}'>{$department['name']}</option>
                    "
                    : $options .= "
                        <option value='{$department['id']}'>{$department['name']}</option>
                    ";
            }
            return $options;
        }

        function getUnitsForDepartment($departmentId=null,$returnData = false){
            if(!$departmentId){
                $departmentId = filterInput('departmentId',false);
            }

            $units = [];
            $sql1 = "SELECT * FROM unit WHERE department_id=$departmentId";
            $results1 = $this->con->query($sql1);
            while($row1 = $results1->fetch_assoc()){
                $units[] = $row1;
            }
            if($returnData){
                return $units;
            }
            else{
                echo json_encode(['units'=>$units]);
            }
        }

        function generateUnitsValues($departmentId=null,$select=null){
            if($departmentId){
                $units = $this->getUnitsForDepartment($departmentId,true);
            }
            else{
                $sql1 = "SELECT id FROM department ORDER BY id LIMIT 1";
                $result1 = $this->con->query($sql1);
                $departmentId = $result1->fetch_assoc()['id'];
                $units = $this->getUnitsForDepartment($departmentId,true);
            }
            $options = "";
            foreach($units as $unit){
                $select == $unit['id']
                    ? $options .= "
                        <option selected value='{$unit['id']}'>{$unit['name']}</option>
                    "
                    : $options .= "
                        <option value='{$unit['id']}'>{$unit['name']}</option>
                    ";
            }
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
            $username = filterInput('username');
            $password = password_hash($username,PASSWORD_BCRYPT);

            $sql1 = "INSERT INTO 
            $this->tableName(surname,other_names,phone,email,employee_role_id,location,username,password,department_id)
            VALUE('$surname','$otherNames','$phone','$email',$role,'$location','$username','$password',$department)";
            if($unit){
                $sql1 = "INSERT INTO 
                $this->tableName(surname,other_names,phone,email,employee_role_id,location,username,password,department_id,unit_id)
                VALUE('$surname','$otherNames','$phone','$email',$role,'$location','$username','$password',$department,$unit)";
            }

            if($this->con->query($sql1)){
                http_response_code(201);
                echo json_encode(['status'=>'SUCCESS']);
            }else{
                http_response_code(400);
                echo json_encode(['status'=>'ERROR']);
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
            $username = filterInput('username');

            $sql1 = "UPDATE $this->tableName 
            SET surname='$surname', other_names='$otherNames', username='$username', phone='$phone', email='$email', location='$location', employee_role_id=$role, department_id=$department, unit_id=null WHERE id=$employeeId";
            if($unit){
                $sql1 = "UPDATE $this->tableName 
                SET surname='$surname', other_names='$otherNames', username='$username', phone='$phone', email='$email', location='$location', employee_role_id=$role, 
                department_id=$department, unit_id=$unit WHERE id=$employeeId";
            }

            if($this->con->query($sql1)){
                http_response_code(201);
                echo json_encode(['status'=>'SUCCESS']);
            }else{
                http_response_code(400);
                echo json_encode(['status'=>'ERROR']);
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