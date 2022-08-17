<?php
    date_default_timezone_set("GMT");
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

        return "($returnValue)";
    }

    function convertToKhebabCase($word,$delimiter){
        $wordLst = explode($delimiter,strtolower($word));
        return implode('-',$wordLst);
    }

    function colorCodesForProgress($progress){
        if($progress >= 0 and $progress <= 25){
            return 'below-average';
        }
        elseif($progress >= 26  and $progress <= 50){
            return 'average';
        }
        elseif($progress >= 51 and $progress <= 75){
            return 'above-average';
        }
        return 'excellent';
    }
    
?>