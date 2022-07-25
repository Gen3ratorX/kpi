<?php
    require_once '../misc/utils.php';

    class EmployeeControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
        }

        function getEmployeesList(){
            $employees = [];
            $sql1 = "SELECT * FROM employee";
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
                                        <button class='btn btn-md btn-4 mb-2'>Delete</button>
                                        <br>
                                        <button class='btn btn-md btn-2'>Edit</button>
                                    </div>
                                </div>
                                <div class='card-body'>
                                    <h4>$name</h4>
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

        function generateRolesValues($roles){
            $options = "";
            foreach($roles as $role){
                $options .= "
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

        function saveEmployee(){
            $surname = filterInput('surname');
            $otherNames = filterInput('otherNames');
            $phone = filterInput('phone');
            $email = filterInput('email') ?: null;
            $location = filterInput('location');
            $role = filterInput('role');
            $unit = filterInput('unit') ?: null;
            $department = filterInput('department') ?: null;
            echo json_encode([$email,$unit,$department]);
        }
    }
?>