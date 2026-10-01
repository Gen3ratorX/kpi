<?php
require_once '../misc/utils.php';

class EmployeeControl
{
    private $con;
    private $tableName;
    private $itemsPerPage;

    function __construct($con)
    {
        $this->con = $con;
        $this->tableName = 'employee';
        $this->itemsPerPage = 40;
    }

    function getEmployeesList($pageNumber, $q, $columns)
    {
        $pageNumber = $pageNumber ? max(1, (int)$pageNumber) : 0;
        $itemsPerPage = $this->itemsPerPage + 1;
        $start = ($pageNumber * $itemsPerPage) - $itemsPerPage;
        $employees = [];
        $exitDate = "(SELECT MAX(exit_date) FROM employee_exit WHERE employee_exit.employee_id = $this->tableName.id) AS exit_date";
        $params = [];
        $sql1 = "SELECT *, $exitDate FROM $this->tableName";
        if ($q) {
            [$condition, $params] = searchCondition($columns, $q);
            $sql1 .= " WHERE $condition";
        }
        if ($pageNumber) {
            $sql1 .= " LIMIT $start, $itemsPerPage";   // both are integers computed above
        }

        $results = $this->con->execute_query($sql1, $params);
        while ($row1 = $results->fetch_assoc()) {
            $employees[] = $row1;
        }
        return $employees;
    }

    function employeeAdminListTemplate($pageNumber = null, $q = null, $columns = null)
    {
        // $pageNumber ??= 1;
        $employees = $this->getEmployeesList($pageNumber, $q, $columns);

        $employeesHtml = "";
        if ($employees) {
            // Pagination
            $paginationFunction = generatePagination($pageNumber, $employees, $this->itemsPerPage);
            $employees = $paginationFunction['data'];
            $paginationHtml = $paginationFunction['paginationHtml'];

            $employeeHtml = "";
            foreach ($employees as $employee) {
                $name = "{$employee['surname']} {$employee['other_names']}";
                $hasLeft = $employee['status'] == 'left';
                $statusAction = $hasLeft
                    ? "<button class='btn btn-md btn-2 mb-2 reinstate-employee' id='employee-{$employee['id']}'>Reinstate</button>"
                    : "<button class='btn btn-md btn-2 mb-2 mark-left-attempt' id='employee-{$employee['id']}' data-bs-toggle='modal' data-bs-target='#markLeft'>Mark as Left</button>";
                $statusLine = $hasLeft
                    ? "<p class='m-0 text-danger'>Left" . ($employee['exit_date'] ? " on {$employee['exit_date']}" : '') . "</p>"
                    : '';
                $employeeHtml .= "
                        <div class='col'>
                            <div class='card shadow-sm h-100 employee action-item'>
                                <div class='options d-flex align-items-center justify-content-center'>
                                    <div>
                                        $statusAction
                                        <br>
                                        <a href='./employee_form.php?id={$employee['id']}' class='btn btn-md btn-4'>Edit</a>
                                    </div>
                                </div>
                                <div class='card-body'>
                                    <h4 class'text-truncate text-secondary'>$name</h4>
                                    <p class='m-0 text-secondary'>Username: <span class='text-dark'>{$employee['username']}</span></p>
                                    <p class='m-0 text-secondary'>Phone: <span class='text-dark'>{$employee['phone']}</span></p>
                                    $statusLine
                                </div>
                            </div>
                        </div>
                    ";
            }

            $employeesHtml = "
                    <section id='employees' class='row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3'>
                        $employeeHtml
                    </section>
                    $paginationHtml
                ";
        } elseif ($pageNumber == 1 and !$employees) {
            $employeesHtml .= "
                    <!-- No Item -->
                    <section class='no-item'>
                        No Employee Has Been added.
                        <div>
                            <a href='./employee_form.php' class='btn btn-1 btn-md'> Add Employee </a>
                        </div>
                    </section>
                ";
        } else {
            // redirect
            header('Location: ./employees.php');
            exit;
        }
        return $employeesHtml;
    }

    function generateRolesValues($roles, $select = 0)
    {
        $options = "";
        foreach ($roles as $role) {
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



    function generateDepartmentsValues($departments, $select = 0)
    {
        $options = "";
        foreach ($departments as $department) {
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

    function getUnitsForDepartment($departmentId = null, $returnData = false)
    {
        if (!$departmentId) {
            $departmentId = filterInput('departmentId', false);
        }

        $units = [];
        $results1 = $this->con->execute_query("SELECT * FROM unit WHERE department_id=?", [$departmentId]);
        while ($row1 = $results1->fetch_assoc()) {
            $units[] = $row1;
        }
        if ($returnData) {
            return $units;
        } else {
            echo json_encode(['units' => $units]);
        }
    }

    function generateUnitsValues($departmentId = null, $select = null)
    {
        if ($departmentId) {
            $units = $this->getUnitsForDepartment($departmentId, true);
        } else {
            $sql1 = "SELECT id FROM department ORDER BY id LIMIT 1";
            $result1 = $this->con->query($sql1);
            $departmentId = $result1->fetch_assoc()['id'];
            $units = $this->getUnitsForDepartment($departmentId, true);
        }
        $options = "";
        foreach ($units as $unit) {
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

    function generateUsername($surname, $otherNames)
    {
        $username = '';
        $otherNamesLst = explode(' ', $otherNames);
        foreach ($otherNamesLst as $otherName) {
            $username .= strtolower($otherName[0]);
        }
        $username .= strtolower($surname);

        // Check if it exists in the database
        $results1 = $this->con->execute_query("SELECT * FROM $this->tableName WHERE username=?", [$username]);
        $numRows = $results1->num_rows + 1;
        $username .= "$numRows";
        return $username;
    }


    function getEmployee($employeeId)
    {
        $result1 = $this->con->execute_query("SELECT * FROM $this->tableName WHERE id=?", [$employeeId]);
        return $result1->fetch_assoc();
    }


    function saveEmployee()
    {
        $surname = filterInput('surname');
        $otherNames = filterInput('otherNames');
        $phone = filterInput('phone');
        $email = filterInput('email');
        $location = filterInput('location');
        $role = filterInput('role');
        $unit = filterInput('unit');
        $department = filterInput('department');
        $username = filterInput('username');
        $password = password_hash($username, PASSWORD_BCRYPT);
        $hireDate = $this->hireDateValue();
        if ($hireDate === false) {
            return;
        }

        $sql1 = "INSERT INTO 
            $this->tableName(surname,other_names,phone,email,employee_role_id,location,username,password,department_id,unit_id,hire_date)
            VALUE(?,?,?,?,?,?,?,?,?,?,?)";
        $params = [$surname, $otherNames, $phone, $email, $role, $location, $username, $password, $department, $unit ?: null, $hireDate];
        $this->respondToSave($sql1, $params, 201);
    }

    function editEmployee()
    {
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
        $hireDate = $this->hireDateValue();
        if ($hireDate === false) {
            return;
        }

        $sql1 = "UPDATE $this->tableName 
            SET surname=?, other_names=?, username=?, phone=?, email=?, location=?, employee_role_id=?, department_id=?, unit_id=?, hire_date=?
            WHERE id=?";
        $params = [$surname, $otherNames, $username, $phone, $email, $location, $role, $department, $unit ?: null, $hireDate, $employeeId];
        $this->respondToSave($sql1, $params, 201);
    }

    function respondToSave($sql, $params, $successCode)
    {
        try {
            $this->con->execute_query($sql, $params);
            http_response_code($successCode);
            echo json_encode(['status' => 'SUCCESS']);
        } catch (mysqli_sql_exception $e) {
            // 1062 = duplicate username or email; the form shows "already taken" for a 400
            http_response_code($e->getCode() == 1062 ? 400 : 500);
            echo json_encode(['status' => 'ERROR']);
        }
    }

    // Returns the posted hire date ('Y-m-d', or null when blank), or false after
    // sending a 400 when it isn't a real date
    function hireDateValue()
    {
        $hireDate = filterInput('hireDate');
        if (!$hireDate) {
            return null;
        }
        $date = DateTime::createFromFormat('!Y-m-d', $hireDate);
        if (!$date || $date->format('Y-m-d') !== $hireDate) {
            http_response_code(400);
            echo json_encode(['status' => 'ERROR', 'message' => 'Invalid hire date']);
            return false;
        }
        return $hireDate;
    }

    // Employees are never deleted: that would cascade away their tasks and every rating
    // they gave or received. Leaving is recorded instead, which the turnover model learns from.
    function markEmployeeLeft()
    {
        $employeeId = (int)filterInput('employeeId');
        $exitDate = filterInput('exitDate');
        $exitType = filterInput('exitType');
        $reason = filterInput('reason') ?: null;

        $date = DateTime::createFromFormat('!Y-m-d', (string)$exitDate);
        $exitTypes = ['resigned', 'dismissed', 'retired', 'contract_ended', 'other'];
        if ($employeeId <= 0 || !$date || $date->format('Y-m-d') !== $exitDate || !in_array($exitType, $exitTypes, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'ERROR', 'message' => 'Please provide a valid exit date and type']);
            return;
        }

        $this->con->begin_transaction();
        $stmt1 = $this->con->prepare("UPDATE $this->tableName SET status='left' WHERE id=? AND status='active'");
        $stmt1->bind_param('i', $employeeId);
        $stmt1->execute();
        if ($stmt1->affected_rows != 1) {
            $this->con->rollback();
            http_response_code(409);
            echo json_encode(['status' => 'ERROR', 'message' => 'Employee not found or already marked as left']);
            return;
        }
        $stmt2 = $this->con->prepare("INSERT INTO employee_exit(employee_id,exit_date,exit_type,reason) VALUES(?,?,?,?)");
        $stmt2->bind_param('isss', $employeeId, $exitDate, $exitType, $reason);
        if ($stmt2->execute()) {
            $this->con->commit();
            echo json_encode(['status' => 'SUCCESS']);
        } else {
            $this->con->rollback();
            http_response_code(500);
            echo json_encode(['status' => 'ERROR']);
        }
    }

    // For mistakes and rehires. The exit record is kept as history.
    function reinstateEmployee()
    {
        $employeeId = (int)filterInput('employeeId');
        $stmt1 = $this->con->prepare("UPDATE $this->tableName SET status='active' WHERE id=? AND status='left'");
        $stmt1->bind_param('i', $employeeId);
        $stmt1->execute();
        if ($stmt1->affected_rows == 1) {
            echo json_encode(['status' => 'SUCCESS']);
        } else {
            http_response_code(409);
            echo json_encode(['status' => 'ERROR', 'message' => 'Employee not found or already active']);
        }
    }
}
