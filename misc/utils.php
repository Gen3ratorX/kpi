<?php
    function filterInput($field,$post=true){
        // if($post){
        // }
        // else{
            
        // }
        // $sanitizedField = $post 
        // ? trim(htmlspecialchars($_POST[$field])) 
        // : trim(htmlspecialchars($_GET[$field]));
        if($post){
            $sanitizedField =  isset($_POST[$field]) ? trim(htmlspecialchars($_POST[$field])) : NULL;
        }else{
            $sanitizedField =  isset($_GET[$field]) ? trim(htmlspecialchars($_GET[$field])) : NULL;
        }
        return $sanitizedField;
    }

?>