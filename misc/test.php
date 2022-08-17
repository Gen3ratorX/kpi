<?php
    require_once 'database_auth.php';
    require_once 'utils.php';
    class TestControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
        }

        function testDatabaseConnection(){
            echo $this->con->connect_error ? "Error Connecting to Database" : "Database Connected Successfully";
        }

        function generateUsername($surname,$otherNames){
            $username = '';
            // $otherNamesLst = explode()
        }
        
        function createSuperuser($username,$password){
            $hasgedPasssword = password_hash($password,PASSWORD_BCRYPT);
            $sql1 = "INSERT IGNORE INTO admin(username,password) 
            VALUE('$username','$hasgedPasssword')";
            if($this->con->query($sql1)){
                echo "Superuser created successfully";
            }
            else{
                echo "Error in creating superuser";
            }
        }
    }

    $testControl = new TestControl($con);
    // $testControl->createSuperuser('eoffei','crescue7536')
    echo colorCodesForProgress(0);
?>