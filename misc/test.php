<?php
    require_once 'database_auth.php';
    class TestControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
        }
        function testDatabaseConnection(){
            echo $this->con->connect_error ? "Error Connecting to Database" : "Database Connected Successfully";
        }
    }

    $testControl = new TestControl($con);
?>