
<?php
    $pageTitle = "NLA KPI Admin | Create";
    require_once 'admin_navbar.php';
?>
    <main id='main-body'
        <div class="container">
            <section class="mb-3">
                <h1 class="header">Create Item</h1>
                <div class="row row-cols-md-2 row-cols-xl-3 g-3">
                    <div class="col">
                        <a href="project_form.php" class="card shadow-sm item">
                            <div class="card-body p-5 text-center">
                                <h2 class="text-secondary">Project</h2>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="./employee_form.php" class="card shadow-sm item">
                            <div class="card-body p-5 text-center">
                                <h2 class="text-secondary">Employee</h2>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="role_form.php" class="card shadow-sm item">
                            <div class="card-body p-5 text-center">
                                <h2 class="text-secondary">Role</h2>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="./department_form.php" class="card shadow-sm item">
                            <div class="card-body p-5 text-center">
                                <h2 class="text-secondary">Department</h2>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="./unit_form.php" class="card shadow-sm item">
                            <div class="card-body p-5 text-center">
                                <h2 class="text-secondary">Unit</h2>
                            </div>
                        </a>
                    </div>
                </div>
        </div>
    </main>
<?php
    require_once 'admin_footer.php';
?>