
<?php
    $todaysDate = date('Y-m-d');
?>

<?php
    $pageTitle = "NLA KPI Admin | Create Project";
    require_once 'admin_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Create Project</h1>
                <section class="row g-3">
                    <div class="col-12">
                        <label for="project-name" class="required">Project Name</label>
                        <input type="text" id="projectName" value="" class="form-control">
                        <div class="invalid-feedback">
                            Please provide a project name.
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="project-deadline" class="required">Deadline:</label>
                        <input type="date" min="<?php echo $todaysDate;?>" id="projectDeadline" value="" class="form-control">
                        <div class="invalid-feedback">
                            Please provide a deadline.
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 align-self-center" id="isOpen" data-value="no">
                        <label>
                            Is Open:
                        </label> <br>
                        <button class="btn btn-4-outline btn-sm">Yes</button>
                        <button class="btn btn-4-solid btn-sm">No</button>
                    </div>
                    <div class="col-12">
                        <label for="projectEmployees">Employees Assigned:</label>
                        <textarea name="" id="projectEmployees" disabled class="form-control"></textarea>
                        <div class="invalid-feedback">
                            Please assign at least one employee to the project;
                        </div>
                    </div>
                </section>

                <!-- Add Employees -->
                <h1 class="header">Assign Employees</h1>
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                God Class
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="employee-item">
                                    God The Father
                                </div>
                                <div class="employee-item selected-employee">
                                    God The Son
                                </div>
                                <div class="employee-item selected-employee">
                                    God The Holy Spirit
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Human Class
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="employee-item">
                                    Peter
                                </div>
                                <div class="employee-item selected-employee">
                                    Paul
                                </div>
                                <div class="employee-item-item selected-employee">
                                    John
                                </div>
                            </div>
                        </div>
                    </div>    
                </div>
            </section>
            <!-- Save -->
            <div class="text-center my-5">
                <button type="button" id="save-project" class="btn btn-1 btn-lg">Save Project</button>
            </div>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>