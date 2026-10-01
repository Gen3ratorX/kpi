<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['adminLoggedIn'])) {
        // AJAX calls (POST, or GET with a task) get a JSON error instead of a redirect
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' or isset($_GET['task'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ERROR', 'message' => 'Not authenticated']);
        } else {
            header("Location: ../");
        }
        exit;
    }
?>