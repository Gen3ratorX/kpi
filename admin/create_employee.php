
<?php
    // require_once '../misc/database_auth.php';
    // require_once '../controls/project_control.php';
    // $projectControl = new ProjectControl($con);
    // $projects = $projectControl->getProjectsList();
    // $projectsHtml = $projectControl->projectAdminListTemplate($projects);
?>

<?php
    $pageTitle = "NLA KPI Admin | Create Employee";
    require_once 'admin_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Create Employee</h1>
                <section class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="surname" class="required">Surame:</label>
                        <input type="text" class="form-control" id="surname">
                        <div class="invalid-feedback">
                            Please provide a first name.
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="otherNames" class="required">Other Names:</label>
                        <input type="text" id="otherNames" class="form-control">
                        <div class="invalid-feedback">
                            Please provide other names.
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="phone" class="required">Phone</label>
                        <input type="text" id="phone" class="form-control">
                        <div class="invalid-feedback">
                            Please provide a phone number.
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="email">Email:</label>
                        <input type="email" id="email" class="form-control">
                        <div class="invalid-feedback">
                            Please provide an email.
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="location" class="required">Location:</label>
                        <input type="text" id="location" class="form-control">
                        <div class="invalid-feedback">
                            Please provide an email.
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="role" class="required">Role:</label>
                        <select id="role" class='form-control'>
                            <option value="">Hello</option>
                        </select>
                        <div class="invalid-feedback">
                            Please provide a role.
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="department">Department:</label>
                        <select id="department" class='form-control'>
                            <option value=""></option>
                            <option value="">Hello</option>
                        </select>
                        <div class="invalid-feedback">
                            Please provide an department.
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="unit">Unit:</label>
                        <select id="unit" class='form-control'>
                            <option value=""></option>
                            <option value="">Hello</option>
                        </select>
                        <div class="invalid-feedback">
                            Please provide an depa.
                        </div>
                    </div>
                </section>
                <div class="text-center my-5">
                    <button type="button" class="btn btn-1 btn-lg">Save Employee</button>
                </div>
            </section>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>