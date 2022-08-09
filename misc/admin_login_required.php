<?php
    session_start();
    if((isset($_SESSION['adminLoggedIn']) and !$_SESSION['adminLoggedIn']) or !isset($_SESSION['adminLoggedIn'])){
        header("Location: ../");
    }
?>