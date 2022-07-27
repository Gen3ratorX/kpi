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

    function spreadSearchColumns($columns,$q){
        $returnValue = "";
        for($i = 0; $i < count($columns); $i++){  
            $i + 1 < count($columns)
            ? $returnValue .= "$columns[$i] LIKE '%$q%' OR "
            : $returnValue .= "$columns[$i] LIKE '%$q%'";
        }

        return $returnValue;
    }
    
    // echo spreadSeachColumns(['name','other_names'],'hrllo');

?>