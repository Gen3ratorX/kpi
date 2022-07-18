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
    <!-- Navbar -->
    <nav class="py-2 px-3 border-bottom">
        <!-- Hamburger -->
        <div id="hamburger">
            <p></p>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <!-- Nav brand -->
            <a href="index.php" class="d-flex align-items-center" id="nav-brand">
                <img src="../static/images/nla-logo.png" width="auto" alt="NLA">
                <h1>National Lottery Authority <br> Key Performance Index</h1>
            </a>
            <!-- Desktop Menu -->
            <div id="desktop-menu">
                <ul>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li class="logout">
                        <a href="">Logout</a>
                    </li>
                    <li class="add-role">
                        <a href="add-role.php">Add Role</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Phone Menu -->
        <div class="d-flex" id="phone-menu-wrapper">
            <div id="phone-menu">
                <a href="index.php">
                    <img src="../static/images/nla-logo.png" alt="NLA Logo">
                    <h1>National Lottery Authority <br> Key Performance Index</h1>
                </a>
                <ul>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li class="logout">
                        <a href="">Logout</a>
                    </li>
                    <li class="add-role">
                        <a href="add-role.php">Add Role</a>
                    </li>
                </ul>
            </div>
            <div id="alternate-close">

            </div>
        </div>
    </nav>