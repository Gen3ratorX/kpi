<?php
    class TaskControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
        }

        function saveTask(){
            echo json_encode($_POST);
        }
        
        function editTask(){
            echo json_encode($_POST);
        }

        function deleteTask(){
            echo json_encode($_POST);
        }
    }
?>