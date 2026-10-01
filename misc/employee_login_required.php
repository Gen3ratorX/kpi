<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Someone marked as left loses access immediately, not just at their next sign-in
    if (!empty($_SESSION['employeeLoggedIn'])) {
        require_once __DIR__ . '/database_auth.php';
        $statusResult = $con->execute_query("SELECT status FROM employee WHERE id=?", [(int)$_SESSION['employeeId']]);
        $statusRow = $statusResult ? $statusResult->fetch_assoc() : null;
        if (!$statusRow || $statusRow['status'] != 'active') {
            $_SESSION = [];
            session_destroy();
        }
    }
    if (empty($_SESSION['employeeLoggedIn'])) {
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