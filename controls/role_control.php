<?php
    class RoleControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
            $this->tableName = 'employee_role';
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
            $sql1 = "INSERT IGNORE INTO $this->tableName(name) VALUE('$name')";
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
            $sql1 = "SELECT * FROM $this->tableName";
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
                            <section href='#' class='card shadow-sm role h-100 text-dark item'>
                                <h2>{$role['name']}</h2>
                                <div class='card-footer'>
                                    <div>
                                        <button class='btn btn-3 delete-role-attempt' data-bs-toggle='modal' data-bs-target='#deleteItem' id='role-{$role['id']}'>Delete</button>
                                        <a class='btn btn-3' href='./role_form.php?id={$role['id']}'>Edit</a>
                                    <div>
                                </div>
                            </section>
                        </div>
                    ";
                }

                $rolesHtml = "
                    <div class='row gy-3 row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4' id='roles'>
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
                            <a href='role_form.php' class='btn btn-1 btn-md'> Add Role </a>
                        </div>
                    </section>
                ";
            }
            return $rolesHtml;
        }

        function getRole($roleId){
            $sql1 = "SELECT * FROM $this->tableName WHERE id=$roleId";
            $result1 = $this->con->query($sql1);
            return $result1->num_rows == 1 
            ? $result1->fetch_assoc()
            : null;
        }

        function editRole(){
            $roleId = $this->filterInput('roleId');
            $name = $this->filterInput('role');
            $sql1 = "UPDATE $this->tableName SET name='$name' WHERE id=$roleId";
            if($this->con->query($sql1)){
                echo json_encode([
                    'status'=>"SUCCESS",
                ]);
            }
            else{
                http_response_code(500);
                echo json_encode([
                    'status'=>"ERROR"
                ]);
            }

        }

        function deleteRole(){
            $roleId = $this->filterInput('roleId');
            $sql1 = "DELETE FROM $this->tableName WHERE id=$roleId";
            if($this->con->query($sql1)){
                http_response_code(204);
                echo json_encode([
                    'status'=>"SUCCESS",
                ]);
            }
            else{
                http_response_code(500);
                echo json_encode([
                    'status'=>"ERROR"
                ]);
            }
        }
    }
?>