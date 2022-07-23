<?php
    class RoleControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;    
        }

        function filterInput($field,$post=true){
            // if($post){
            // }
            // else{
                
            // }
            $sanitizedField = $post 
            ? trim(htmlspecialchars($_POST[$field])) 
            : trim(htmlspecialchars($_GET[$field]));
            return $sanitizedField;
        }

        function saveRole(){
            $name = $this->filterInput('role');
            $sql1 = "INSERT IGNORE INTO employee_role(name) VALUE('$name')";
            if($this->con->query($sql1)){
                http_response_code(201);
                echo json_encode([
                    'status'=> "SUCCESS"
                ]);
            }
            else{
                http_response_code(500);
                echo json_encode([
                    'status'=> "ERROR"
                ]);
            }
        }

        function getRolesList(){
            $roles = [];
            $sql1 = "SELECT * FROM employee_role";
            $results1 = $this->con->query($sql1);
            if($results1->num_rows > 0){
                while($row = $results1->fetch_assoc()){
                    $roles[] = $row;
                }
            }
            return $roles;
        }

        function roleAdminListTemplate(){
            $roles = $this->getRolesList();
            $rolesHtml = "";
            if($roles){
                $roleHtml = "";
                foreach($roles as $role){
                    $roleHtml .= "
                        <div class='col'>
                            <a href='#' class='card shadow-sm role h-100 text-dark item'>
                                <h2>{$role['name']}</h2>
                            </a>
                        </div>
                    ";
                }

                $rolesHtml = "
                    <div class='row gy-3 row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4'>
                        $roleHtml
                    </div>
                ";
            }
            else{
                $rolesHtml .= "
                    <!-- No Item -->
                    <section class='no-item'>
                        No Role Has Been added.
                        <div>
                            <a href='create_role.php' class='btn btn-1 btn-md'> Add Role </a>
                        </div>
                    </section>
                ";
            }
            return $rolesHtml;
        }
    }
?>