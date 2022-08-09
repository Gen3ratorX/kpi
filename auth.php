<?php
    require_once './misc/database_auth.php';
    require_once './misc/utils.php';

    if(isset($_POST['task']) and $_POST['task'] == 'signIn'){
        $username = filterInput('username');
        $password = filterInput('password');

        // $sql1 = "SELECT * FROM employee 
        // WHERE username='$username'";
        $sql1  = "SELECT `employee`.`id`, `employee`.`password`, `employee_role`.`role` FROM employee
        INNER JOIN employee_role ON `employee`.`employee_role_id` = `employee_role`.`id`
        WHERE username = '$username'";
        $result1 = $con->query($sql1);
        if($result1->num_rows == 1){
            $employee = $result1->fetch_assoc();
            $dbPassword = $employee['password'];
            if(password_verify($password,$dbPassword)){
                session_start();
                $_SESSION['employeeLoggedIn'] = true;
                $_SESSION['employeeUsername'] = $username;
                $_SESSION['employeeId'] = $employee['id'];
                $_SESSION['employeeRole'] = $employee['role'];
                echo json_encode(['status'=>'SUCCESS']);
            }
            else{
                http_response_code(400);
                echo json_encode([
                    'status'=>'ERROR'
                ]);
            }
        }
        else{
            http_response_code(400);
            echo json_encode([
                'status'=>'ERROR'
            ]);
        }
    }
?>