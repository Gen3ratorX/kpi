<?php
    // session_start();
    // header("Location: admin/");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="./static/css/bootstrap.min.css" rel="stylesheet">
    <link href="./static/css/base.css" rel="stylesheet">
    <link rel="stylesheet" href="./static/css/auth.css">
    <title>Sign In</title>
    <style>
        body{
            background-image: url('./static/images/auth_background.jpg');
            background-position: center top;
            background-attachment: cover;
            background-repeat: no-repeat;
            background-size: fixed;
        }
        .form{
            position: relative;
            z-index: 1;
            padding: 10px;
            max-width: 400px;
            text-align: center;
            border-radius: 5px;
            margin: 100px auto 0 auto;
        }
        .form label{
            display: block;
            text-align: left;
        }
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

    <section class="form">
        <h3 class="mb-4">Sign In To Continue</h3>
        <div>
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username">
            <div class="invalid-feedback text-start">Please provide a username.</div>
        </div>
        <div class="mt-4">
            <label for="password">Password:</label>
            <input type="password" id="password" class="form-control">
            <div class="invalid-feedback text-start">Please provide a password</div>
        </div>
        <div class="my-3 text-center">
            <button id="sign-in" class="btn btn-lg btn-1">Sign In</button>
        </div>
    </section>
    <script src="./static/js/jquery-3.6.0.min.js"></script>
    <script src="./static/js/bootstrap.bundle.min.js"></script>
    <script src="./static/js/base.js"></script>
    <script src="./static/js/auth.js"></script>
</body>
</html>