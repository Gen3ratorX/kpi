<?php
    if(isset($_POST) and $_POST['task'] == 'saveProject'){
        echo json_encode("God is good");
    }
    elseif (isset($_POST) and $_POST['task'] == 'saveEmployee') {
        echo json_encode("God is very good");
    }
    elseif (isset($_POST) and $_POST['task'] == 'saveRole') {
        // http_response_code(400);
        echo json_encode("God is very very good");
    }
?>