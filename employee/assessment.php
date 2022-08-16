<?php
    $employeesAssignmentHtml  = $taskControl->generateEmployeesForProjectHtml($employeeRole,$projectId,$employeeId);
    // $employees = $taskControl->getEmployeesForProject($employeeRole,$projectId,$employeeId);
    // echo print_r($employees);
?>

<h1 class="header">Assessment</h1>
<section class="mb-4" id="assessment-attempt-wrapper" data-is-assessment-open="<?php echo $assessProject;?>">
    <div class="row g-3">
        <?php echo $employeesAssignmentHtml; ?>
    </div>
</section>

<section id='assessment-wrapper'>
    <!-- <div class="accordion" id="accordionExample">
        <div class="accordion-item" id="task-1">
            <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Task 1
            </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <p class="alert alert-danger d-none error">You have to rate the task before saving.</p>
                <div>
                    <label for="" class="required">Rating:</label>
                    <div class="row g-3">
                        <div class="col-auto">
                            <p class="rating selected-rating">10%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">20%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">30%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">40%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">50%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">60%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">70%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">80%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">90%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">100%</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="required">Comments:</label>
                    <textarea class="form-control"></textarea>
                </div>
                <div class="text-center my-3">
                    <button type="button" class="btn btn-1 btn-md save-assessment" data-task-id="task-1">Save</button>
                </div>
            </div>
            </div>
        </div>
        <div class="accordion-item" id="task-2">
            <h2 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                Task 2
            </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <section class="d-flex justify-content-between align-items-center">
                    Rating: 
                    <h1 class="display-6 text-primary">
                        <strong>10%</strong>
                    </h1>
                </section>
                <section>
                    <p class="m-0">Comments</p>
                    <div class="lead text-secondary">
                        No Comments
                    </div>
                    <div class="lead text-secondary">
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Ullam tempora vitae fuga sunt quos sed odio praesentium magnam, nemo cupiditate accusantium! Maiores nam impedit molestias laudantium perspiciatis! Sed, et quod?
                    </div>
                </section>
            </div>
            </div>
        </div>
        <div class="accordion-item" id="task-3">
            <h2 class="accordion-header" id="headingThree">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                Task 3
            </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <p class="alert alert-danger d-none error">You have to rate the task before saving.</p>
                <div>
                    <label for="" class="required">Rating:</label>
                    <div class="row g-3">
                        <div class="col-auto">
                            <p class="rating selected-rating">10%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">20%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">30%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">40%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">50%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">60%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">70%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">80%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">90%</p>
                        </div>
                        <div class="col-auto">
                            <p class="rating">100%</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="required">Comments:</label>
                    <textarea name="" id="" class="form-control"></textarea>
                </div>
                <div class="text-center my-3">
                    <button type="button" class="btn btn-1 btn-md save-assessment" data-task-id="task-1">Save</button>
                </div>
            </div>
            </div>
        </div>
    </div> -->
    <div class="text-center my-3">
        <button type="button" id="close-assessment" class="btn btn-2 btn-lg">Close</button>
    </div>
</section>