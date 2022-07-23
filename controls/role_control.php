<?php
    class RoleControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;    
        }

        function saveRole(){

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