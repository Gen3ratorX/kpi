<?php
    require_once '../misc/utils.php';
    class RoleControl{
        private $con;
        private $tableName;

        function __construct($con)
        {
            $this->con = $con;
            $this->tableName = 'employee_role';
        }

        function saveRole(){
            $name = filterInput('name');
            $role = filterInput('role');
            $sql1 = "INSERT IGNORE INTO $this->tableName(name,role) VALUE(?,?)";
            if($this->con->execute_query($sql1, [$name, $role])){
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

        function getRolesList($q=null,$columns=null){
            $roles = [];
            $params = [];
            if($q){
                [$condition, $params] = searchCondition($columns,$q);
                $sql1 = "SELECT * FROM $this->tableName WHERE $condition";
            }
            else{
                $sql1 = "SELECT * FROM $this->tableName";
            }
            $results1 = $this->con->execute_query($sql1, $params);
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
                                <h4 class='text-center text-secondary'>{$role['name']}</h4>
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
                    // $role['can_delete'] 
                    // ? $roleHtml .= "
                    //     <div class='col'>
                    //         <section class='card h-100 shadow role h-100 text-dark py-5 px-2 action-item'>
                    //             <h4 class='text-center text-secondary'>{$role['name']}</h4>
                    //             <div class='options d-flex align-items-center justify-content-center'>
                    //                 <div class='text-center'>
                    //                     <button title='Delete Role' class='mb-2 btn btn-2 delete-role-attempt btn-md' data-bs-toggle='modal' data-bs-target='#deleteItem' id='role-{$role['id']}'>
                    //                         Delete
                    //                     </button> <br/>
                    //                     <a title='Edit Role' class='btn btn-4 btn-md' href='./role_form.php?id={$role['id']}'>
                    //                         Edit
                    //                     </a>
                    //                 <div>
                    //             </div>
                    //         </section>
                    //     </div>
                    // "
                    // : $roleHtml .= "
                    //     <div class='col'>
                    //         <section class='card h-100 shadow role h-100 text-dark py-5 px-2 action-item'>
                    //             <h4 class='text-center text-secondary'>{$role['name']}</h4>
                    //         </section>
                    //     </div>
                    // ";
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
            $result1 = $this->con->execute_query("SELECT * FROM $this->tableName WHERE id=?", [$roleId]);
            return $result1->num_rows == 1 
            ? $result1->fetch_assoc()
            : null;
        }

        function editRole(){
            $roleId = filterInput('roleId');
            $role = filterInput('role');
            $name = filterInput('name');
            $sql1 = "UPDATE $this->tableName SET name=?, role=? WHERE id=?";
            if($this->con->execute_query($sql1, [$name, $role, $roleId])){
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
            $roleId = (int)filterInput('roleId');
            // Employees (including ones who have left) must be moved off a role before it can go.
            // The database also refuses it (ON DELETE RESTRICT), so this is just the friendly message.
            $stmt1 = $this->con->prepare("SELECT COUNT(*) AS employees FROM employee WHERE employee_role_id=?");
            $stmt1->bind_param('i', $roleId);
            $stmt1->execute();
            $employeeCount = (int)$stmt1->get_result()->fetch_assoc()['employees'];
            if($employeeCount > 0){
                http_response_code(409);
                $noun = $employeeCount == 1 ? 'employee still has' : 'employees still have';
                echo json_encode([
                    'status'=>"ERROR",
                    'message'=>"$employeeCount $noun this role. Move them to another role first.",
                ]);
                return;
            }
            if($this->con->execute_query("DELETE FROM $this->tableName WHERE id=?", [$roleId])){
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