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
        // Normal user is trying to sign in
        if($result1->num_rows == 1){
            $employee = $result1->fetch_assoc();
            $dbPassword = $employee['password'];
            if(password_verify($password,$dbPassword)){
                session_start();
                $_SESSION['employeeLoggedIn'] = true;
                $_SESSION['employeeUsername'] = $username;
                $_SESSION['employeeId'] = $employee['id'];
                $_SESSION['employeeRole'] = $employee['role'];
                echo json_encode(['status'=>'SUCCESS','employee'=>true]);
            }
            else{
                http_response_code(400);
                echo json_encode([
                    'status'=>'ERROR'
                ]);
            }
        }
        else{
            // Check if admin is signing in
            $sql2 = "SELECT id,username,password FROM admin WHERE username='$username'";
            $results2 = $con->query($sql2);
            if($results2->num_rows == 1){
                $admin = $results2->fetch_assoc();
                $dbPassword = $admin['password'];
                if(password_verify($password,$dbPassword)){
                    $adminId = $admin['id'];
                    $currentDateTime = new DateTime();

                    // Update last login
                    $sql3 = "UPDATE admin SET last_login='{$currentDateTime->format('Y-m-d H:i:s')}' WHERE id=$adminId";
                    if($con->query($sql3)){
                        session_start();
                        $_SESSION['adminLoggedIn'] = true;
                        $_SESSION['adminUsername'] = $username;
                        echo json_encode(['status'=>'SUCCESS','employee'=>false]);
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
            else{
                http_response_code(400);
                echo json_encode([
                    'status'=>'ERROR'
                ]);
            }
        }
    }
?>