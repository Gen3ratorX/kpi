<?php
    class ProjectControl{
        private $con;

        function __construct($con)
        {
            $this->con = $con;
        }

        function getProjectsList(){
            $sql1 = "SELECT * FROM project";
            $projects = [];
            $results1 = $this->con->query($sql1);
            if($results1->num_rows > 0){
                while($row = $results1->fetch_assoc()){
                    $projects[] = $row;
                }
            }
            return $projects;
        }

        function projectAdminListTemplate($projects){
            $projectsHtml = "";
            if($projects){
                $projectHtml = "";
                foreach($projects as $project){
                    $projectHtml .= "
                        <div class='col'>
                            <a href='#' class='card shadow-sm role h-100 project text-dark'>
                                <h2>{$project['name']}</h2>
                                <p class='text-muted m-0'>Date Created: <span class='text-dark'>2nd May, 2022</span></p>
                                <p class='text-muted m-0'>Deadline: <span class='text-dark'>21st October, 2022</span></p>
                            </a>
                        </div>
                    ";
                }

                $projectsHtml = "
                    <div class='row gy-3 row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4'>
                        $projectHtml
                    </div>
                ";
            }
            else{
                $projectsHtml .= "
                    <!-- No Item -->
                    <section class='no-item'>
                        No Project Has Been added.
                        <div>
                            <a href='add_project.php' class='btn btn-1 btn-md'> Add Project </a>
                        </div>
                    </section>
                ";
            }
            return $projectsHtml;
        }
    }
?>
