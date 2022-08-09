<?php
    session_start();
    if((isset($_SESSION['employeeLoggedIn']) and !$_SESSION['employeeLoggedIn']) or !isset($_SESSION['employeeLoggedIn'])){
        header("Location: ../");
    }
?>