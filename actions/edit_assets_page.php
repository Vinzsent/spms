<?php
include '../includes/auth.php';
include '../includes/db.php';

// Handle Add Budget form submission coming from pages/assets_page.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_budget'])) {
    $budget_max = trim($_POST['budget_max'] ?? '');
    $course_code = trim($_POST['course_name'] ?? '');

    if ($budget_max === '') {
        $_SESSION['error'] = 'Please enter a budget amount.';
    } else {
        $stmt = $conn->prepare("INSERT INTO budget (budget_max, course_name) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param('ss', $budget_max, $course_code);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Budget added successfully.';
            } else {
                $_SESSION['error'] = 'Failed to add budget.';
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Database error while adding budget.';
        }
    }
}

// Redirect back to the budgets page
header('Location: ../pages/assets_page.php');
exit;
