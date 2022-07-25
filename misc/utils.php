<?php
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
?>