<?php
    require_once '../misc/utils.php';

    class UnitControl{
        private $con;
        private $tableName;

        function __construct($con)
        {
            $this->con = $con;
            $this->tableName = 'unit';
        }

        function getUnitsList($q=null,$columns=null){
            $units = [];
            $params = [];
            if($q){
                [$condition, $params] = searchCondition($columns,$q);
                $sql1 = "SELECT * FROM $this->tableName WHERE $condition";
            }
            else{
                $sql1 = "SELECT * FROM $this->tableName";
            }
            $results1 = $this->con->execute_query($sql1, $params);
            while($row1 = $results1->fetch_assoc()){
                $units[] = $row1;
            }
            return $units;
        }   

        function unitAdminListTemplate(){
            $units = $this->getUnitsList();
            $unitsHtml = "";
            if($units){
                $unitHtml = "";
                foreach($units as $unit){
                    $unitHtml .= "
                        <div class='col'>
                            <section class='card h-100 shadow role h-100 text-dark py-5 px-2 action-item'>
                                <h4 class='text-center text-secondary'>{$unit['name']}</h4>
                                <div class='options d-flex align-items-center justify-content-center'>
                                    <div class='text-center'>
                                        <button title='Delete Unit' class='mb-2 btn btn-2 delete-unit-attempt btn-md' data-bs-toggle='modal' data-bs-target='#deleteItem' id='unit-{$unit['id']}'>
                                            Delete
                                        </button> <br/>
                                        <a title='Edit Unit' class='btn btn-4 btn-md' href='./unit_form.php?id={$unit['id']}'>
                                            Edit
                                        </a>
                                    <div>
                                </div>
                            </section>
                        </div>
                    ";
                }

                $unitsHtml = "
                    <section class='row gy-3 row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4' id='units'>
                        $unitHtml
                    </section>
                ";
            }
            else{
                $unitsHtml .= "
                    <!-- No Item -->
                    <section class='no-item'>
                        No Unit Has Been added.
                        <div>
                            <a href='unit_form.php' class='btn btn-1 btn-md'> Add Unit </a>
                        </div>
                    </section>
                ";
            }
            return $unitsHtml;
        }

        function getUnit($unitId){
            $result1 = $this->con->execute_query("SELECT * FROM unit WHERE id=?", [$unitId]);
            return $result1->fetch_assoc();
        }

        function generateDepartmentOptions($departments,$departmentId = null){
            $options = "";
            foreach($departments as $department){
                $departmentId == $department['id']
                ? $options .= "
                    <option selected value='{$department['id']}'>{$department['name']}</option>
                "
                : $options .= "
                    <option value='{$department['id']}'>{$department['name']}</option>
                ";
            }
            return $options;
        }

        function saveUnit(){
            $name = filterInput('unit');
            $departmentId = filterInput('department');
            $sql1 = "INSERT IGNORE INTO unit(name,department_id) VALUE(?,?)";
            if($this->con->execute_query($sql1, [$name, $departmentId])){
                http_response_code(201);
                echo json_encode([
                    'status'=>"SUCCESS"
                ]);
            }
            else{
                http_response_code(500);
                echo json_encode([
                    'status'=>"ERROR"
                ]);
            }
        }

        function editUnit(){
            $unitId = filterInput('unitId');
            $name = filterInput('unit');
            $departmentId = filterInput('department');

            $sql1 = "UPDATE unit SET name=?, department_id=? WHERE id=?";
            if($this->con->execute_query($sql1, [$name, $departmentId, $unitId])){
                http_response_code(200);
                echo json_encode([
                    'status'=>"SUCCESS"
                ]);
            }
            else{
                http_response_code(500);
                echo json_encode([
                    'status'=>"ERROR"
                ]);
            }
        }

        function deleteUnit(){
            $unitId = filterInput('unitId');
            if($this->con->execute_query("DELETE FROM unit WHERE id=?", [$unitId])){
                http_response_code(200);
                echo json_encode([
                    'status'=>"SUCCESS"
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