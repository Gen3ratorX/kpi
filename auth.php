<?php
    require_once './misc/database_auth.php';
    if(isset($_POST['task']) and $_POST['task'] == 'signIn'){
        echo json_encode("Sign In");
    }
    else{
        echo json_encode("Well");
    }
?>