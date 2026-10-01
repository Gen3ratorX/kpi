<?php
    require_once '../misc/admin_login_required.php';
    require_once '../misc/database_auth.php';
    require_once '../controls/project_control.php';
    // $projects = $projectControl->getProjectsList();
    // $projectsHtml = $projectControl->projectAdminListTemplate($projects);
    
    class AdminControl{
        private $con;
        function __construct($con){
            $this->con = $con;
            $projectControl = new ProjectControl($this->con);
        }
    }
?>