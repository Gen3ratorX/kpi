<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> -->
    <link rel="stylesheet" href="../static/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../static/css/base.css">
    <link rel="stylesheet" href="../static/css/admin.css">
    <title><?php echo $pageTitle; ?></title>
    <style>
        
    </style>
</head>
<body>
    <!-- Information Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast" id="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-info text-white">
                <strong class="me-auto">Information</strong>
                <small>Now</small>
            <button type="button" class="btn-close bg-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastBody">
                Hello, world! This is a toast message.
            </div>
        </div>
    </div>

    <!-- Delete Item Modal -->
    <!-- <div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteItemLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="deleteItemBody" class="text-secondary">
                    </h4>
                    <div class="text-end mt-4">
                        <button class="btn btn-2 btn-sm" id="delete-item">Yes</button>
                        <button class="btn btn-3 btn-sm" data-bs-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Navbar -->
    <nav class="py-2 px-3 border-bottom">
        <!-- Hamburger -->
        <div id="hamburger">
            <p></p>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <!-- Nav brand -->
            <a href="./index.php" class="d-flex align-items-center" id="nav-brand">
                <img src="../static/images/nla-logo.png" width="auto" alt="NLA">
                <h1>National Lottery Authority <br> Target Performance Appraisal</h1>
            </a>
            <!-- Desktop Menu -->
            <div id="desktop-menu">
                <ul>
                    <li>
                        <a href="./index.php">Home</a>
                    </li>
                    <li class="submenu">
                        <a href="#">
                            Extras
                        </a>
                        <ul>
                            <li>
                                <a href="./employees.php">Employees</a>
                            </li>
                            <li>
                                <a href="./roles.php">Roles</a>
                            </li>
                            <li>
                                <a href="./departments.php">Departments</a>
                            </li>
                            <li>
                                <a href="./units.php">Units</a>
                            </li>
                        </ul>
                    </li>
                    <li class="logout">
                        <a href="">Logout</a>
                    </li>
                    <li class="create">
                        <a href="create.php">Create</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Phone Menu -->
        <div class="d-flex" id="phone-menu-wrapper">
            <div id="phone-menu">
                <a href="index.php">
                    <img src="../static/images/nla-logo.png" alt="NLA Logo">
                    <h1>National Lottery Authority <br> Target Performance Appraisal</h1>
                </a>
                <ul>
                    <li>
                        <a href="./index.php">Home</a>
                    </li>
                    <li>
                        <a href="./employees.php">Employees</a>
                    </li>
                    <li>
                        <a href="./roles.php">Roles</a>
                    </li>
                    <li>
                        <a href="./departments.php">Departments</a>
                    </li>
                    <li>
                        <a href="./units.php">Units</a>
                    </li>
                    <li class="logout">
                        <a href="">Logout</a>
                    </li>
                    <li class="create">
                        <a href="create.php">Create</a>
                    </li>
                </ul>
            </div>
            <div id="alternate-close">

            </div>
        </div>
    </nav>