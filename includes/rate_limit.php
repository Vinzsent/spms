<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$limit_seconds = 10; // Only allow 1 submission every 10 seconds

if (isset($_SESSION['last_submission_time'])) {
    $time_passed = time() - $_SESSION['last_submission_time'];
    if ($time_passed < $limit_seconds) {
        $remaining = $limit_seconds - $time_passed;
        echo json_encode([
            'success' => false, 
            'message' => "Too many requests. Please wait $remaining seconds before submitting again."
        ]);
        exit;
    }
}

$_SESSION['last_submission_time'] = time();
?>
