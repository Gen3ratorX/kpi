<?php
    $pageTitle = "NLA KPI Admin | Add Role";
    require_once 'admin_navbar.php';
?>
    
    <!-- Save Focus Modal -->
    <div class="modal fade" id="focusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="focusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="focusModalLabel">Add Focus</h5>
                    <button type="button" class="btn-close" id="close-focus-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="focus-input" class="required">Focus:</label>
                    <input type="text" class="form-control" placeholder="Type in key focuses." id="focus-input">
                    <p class="small text-danger ms-1 mt-1" id="focus-error"></p>
                    <div class="my-4 text-center">
                        <button class="btn btn-1 btn-md" id="save-focus">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Item Modal -->
    <div class="modal fade" id="deleteItemModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteItemModalLabel"></h5>
                    <button type="button" class="btn-close" id="close-delete-item-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="lead"></p>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-4 btn-sm me-3" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-2 btn-sm" id="delete-item">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Objective Modal -->
    <div class="modal fade" id="objectiveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="objectiveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="objectiveModalLabel">Add Objective</h5>
                    <button type="button" class="btn-close" id="close-objective-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="objective-input" class="required">Objective:</label>
                    <input type="text" class="form-control" placeholder="Type in key objectives." id="objective-input">
                    <p class="small text-danger ms-1 mt-1" id="objective-error"></p>
                    <div class="my-4 text-center">
                        <button class="btn btn-1 btn-md" id="save-objective">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main id='main-body'
        <div class="container-lg mb-3">
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
                        <!-- <div class="col-auto">
                            <button class="btn btn-4 btn-sm" id="save-or-edit" data-command='save'>Save</button>
                        </div> -->
                        <div class="col-auto">
                            <button class="btn btn-3 btn-sm add-focus-attempt" data-bs-toggle="modal" data-bs-target="#focusModal">Add Focus</button>
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
                        <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0%">0%</div>
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
                        <a href="#" class="btn btn-1 btn-md add-focus-attempt" data-bs-toggle="modal" data-bs-target="#focusModal"> Add Focus </a>
                    </div>
                </section>
                <section id="focus-wrapper">
                </section>
                    
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>