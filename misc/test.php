<?php
require_once 'database_auth.php';
require_once 'utils.php';
class TestControl
{
    private $con;

    function __construct($con)
    {
        $this->con = $con;
    }

    function testDatabaseConnection()
    {
        echo $this->con->connect_error ? "Error Connecting to Database" : "Database Connected Successfully";
    }

    function generateUsername($surname, $otherNames)
    {
        $username = '';
        // $otherNamesLst = explode()
    }

    function createSuperuser($username, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        if ($this->con->execute_query("INSERT IGNORE INTO admin(username,password) VALUE(?,?)", [$username, $hashedPassword])) {
            echo "Superuser created successfully";
        } else {
            echo "Error in creating superuser";
        }
    }
}

$testControl = new TestControl($con);
// echo colorCodesForProgress(0);
