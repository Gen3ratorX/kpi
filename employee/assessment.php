<?php
    // $employeeRole = 3;
    $employeesAssignmentHtml  = $taskControl->generateEmployeesForProjectHtml($employeeRole,$projectId,$employeeId);
    // $employees = $taskControl->getEmployeesForProject($employeeRole,$projectId,$employeeId);
    // echo print_r($employees);
?>

<section class="mb-4" id="assessment-attempt-wrapper">
    <h1 class="header">Assessment</h1>
    <div class="row g-3">
        <?php echo $employeesAssignmentHtml; ?>
    </div>
</section>

<section id='assessment-wrapper'>
    <h1 class="header">Assessing </h1>
    <div class="accordion" id="accordionExample">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Task 1
            </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
            <div class="accordion-body">
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
                    <button type="button" class="btn btn-1 btn-md">Save</button>
                </div>
            </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                Task 2
            </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
            <div class="accordion-body">
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
                    <button type="button" class="btn btn-1 btn-md">Save</button>
                </div>
            </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                Task 3
            </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
            <div class="accordion-body">
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
                    <button type="button" class="btn btn-1 btn-md">Save</button>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="text-center my-3">
        <button type="button" id="close-assessment" class="btn btn-2 btn-lg">Close</button>
    </div>
</section>