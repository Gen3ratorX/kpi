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

    function getCurrentPageUrl(){
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = explode('?',$protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])[0];

        return $url;
    }

    function generatePagination($pageNumber,$data,$itemsPerPage){
        // Check for next and previous pages
        $hasPrevious = false;
        $hasNext = false;
        $dataCount = count($data);
        if($pageNumber == 1){ // First page
            $hasPrevious = false;
            if($dataCount > $itemsPerPage){
                $hasNext = true;
                array_pop($data);
            }
            else{
                $hasNext = false;
            }
        }
        elseif($pageNumber > 1){
            $hasPrevious = true;
            if($dataCount > $itemsPerPage){
                $hasNext = true;
                array_pop($data);
            }
            else{
                $hasNext = false;
            }
        }

        // Generate Url
        $url = getCurrentPageUrl();

        $prevPageNumber = $pageNumber - 1;
        $nextPageNumber = $pageNumber + 1;
        // Create pagination template
        if($hasNext and $hasPrevious){
            $paginationHtml = "
                <section id='pagination' class='text-center my-3 d-flex align-items-center justify-content-center'>
                    <a href='$url?page=$prevPageNumber' class='btn btn-1 btn-sm'>Prev</a>
                    <span>$pageNumber</span>
                    <a href='$url?page=$nextPageNumber' class='btn btn-1 btn-sm'>Next</a>
                </section>
            ";
        }
        elseif($hasNext and !$hasPrevious){
            $paginationHtml = "
                <section id='pagination' class='text-center my-3 d-flex align-items-center justify-content-center'>
                    <span>$pageNumber</span>
                    <a href='$url?page=$nextPageNumber' class='btn btn-1 btn-sm'>Next</a>
                </section>
            ";
        }
        elseif(!$hasNext and $hasPrevious){
            $paginationHtml = "
                <section id='pagination' class='text-center my-3 d-flex align-items-center justify-content-center'>
                    <a href='$url?page=$prevPageNumber' class='btn btn-1 btn-sm'>Prev</a>
                    <span>$pageNumber</span>
                </section>
            ";
        }
        
        return [
            'data'=>$data,
            'paginationHtml'=>$paginationHtml
        ];
    }
    
?>