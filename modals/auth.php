<?php
session_start();
if (!isset($_SESSION['user'])) {
    // Check if it's an API request or AJAX
    if (strpos($_SERVER['PHP_SELF'], '/api/') !== false || strpos($_SERVER['PHP_SELF'], '/actions/') !== false || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Your session has expired. Please log in again.']);
        exit;
    }

    // Dynamically find the path to index.php at the root
    $rootPath = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false || strpos($_SERVER['PHP_SELF'], '/api/') !== false || strpos($_SERVER['PHP_SELF'], '/actions/') !== false) ? '../index.php' : 'index.php';
    header("Location: $rootPath");
    exit;
}
