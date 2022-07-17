<?php
    $pageTitle = "NLA KPI Admin | Add Role";
    require_once 'admin_navbar.php';
?>
    
    <!-- Add Focus Modal -->
    <div class="modal fade" id="addFocusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addFocusModalL>abel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFocusModalLabel">Add Focus</h5>
                    <button type="button" class="btn-close" id="close-add-focus-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="focus-input" class="required">Focus:</label>
                    <input type="text" class="form-control" placeholder="Type in key focuses." id="focus-input">
                    <p class="small text-danger ms-1 mt-1" id="focus-error"></p>
                    <div class="my-4 text-center">
                        <button class="btn btn-1 btn-md" id="add-focus">Add Focus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <main id='main-body'
        <div class="container mb-3">
            <h1 class="header">Add Role</h1>
            <section class="border-bottom" id="role-progress">
                <!-- Add Role -->
                <section>
                    <label for="role-input" class="required">
                        Role:
                    </label>
                    <div class="row align-items-center gx-2">
                        <div class="col">
                            <input id="role-input" value="Hello" type="text" class="form-control">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-4 btn-sm" id="save-or-edit" data-command='save'>Save</button>
                        </div>
                        <div class="col-auto">
                            <button id="add-focus-attempt" class="btn btn-3 btn-sm" data-bs-toggle="modal" data-bs-target="#addFocusModal">Add Focus</button>
                        </div>
                    </div>
                    <p class="text-danger small ml-5 mb-0 error" data-error="false" id="role-error"></p>
                </section>
                <!-- Progress -->
                <section class="my-4">
                    <h5 class="mb-1 text-muted d-flex justify-content-between">
                        Progress
                        <span class="text-dark">0%</span>
                    </h5>
                    <div class="progress">
                        <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 29%">0%</div>
                    </div>
                </section>
            </section>
            <!-- Focus -->
            <section class="mt-4">
                <h1 class="header">Focuses</h1>
                <!-- No Focus -->
                <section class="no-item">
                    No Focus Has Been Added
                    <div>
                        <a href="#" class="btn btn-1 btn-md" data-bs-toggle="modal" data-bs-target="#addFocusModal"> Add Focus </a>
                    </div>
                </section>
                <section id="focus-wrapper">
                    <!-- <div class="accordion" id="accordionPanelsStayOpenExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                Accordion Item #1
                            </button>
                            </h2>
                            <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                            <div class="accordion-body">
                                <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                            </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                                Accordion Item #2
                            </button>
                            </h2>
                            <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwo">
                            <div class="accordion-body">
                                <strong>This is the second item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                            </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                                Accordion Item #3
                            </button>
                            </h2>
                            <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingThree">
                            <div class="accordion-body">
                                <strong>This is the third item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                            </div>
                            </div>
                        </div>
                    </div> -->
                </section>
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>