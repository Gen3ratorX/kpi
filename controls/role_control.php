<?php
    require_once '../misc/utils.php';
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
            $name = filterInput('role');
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
                            <section class='card h-100 shadow role h-100 text-dark py-5 px-2 action-item'>
                                <h2 class='text-truncate'>{$role['name']}</h2>
                                <div class='options d-flex align-items-center justify-content-center'>
                                    <div class='text-center'>
                                        <button title='Delete Role' class='mb-2 btn btn-2 delete-role-attempt btn-md' data-bs-toggle='modal' data-bs-target='#deleteItem' id='role-{$role['id']}'>
                                            Delete
                                        </button> <br/>
                                        <a title='Edit Role' class='btn btn-4 btn-md' href='./role_form.php?id={$role['id']}'>
                                            Edit
                                        </a>
                                    <div>
                                </div>
                            </section>
                        </div>
                    ";
                }

                $rolesHtml = "
                    <section class='row gy-3 row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4' id='roles'>
                        $roleHtml
                    </section>
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
            $roleId = filterInput('roleId');
            $name = filterInput('role');
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
            $roleId = filterInput('roleId');
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