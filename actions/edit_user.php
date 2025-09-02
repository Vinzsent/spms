<?php
include '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id              = intval($_POST['id']);
    $title           = trim($_POST['title'] ?? '');
    $first_name      = trim($_POST['first_name'] ?? '');
    $middle_name     = trim($_POST['middle_name'] ?? '');
    $last_name       = trim($_POST['last_name'] ?? '');
    $suffix          = trim($_POST['suffix'] ?? '');
    $academic_title  = trim($_POST['academic_title'] ?? '');
    $user_type       = trim($_POST['user_type'] ?? '');
    $username        = trim($_POST['username'] ?? '');
    $password        = $_POST['password'] ?? '';

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user SET title=?, first_name=?, middle_name=?, last_name=?, suffix=?, academic_title=?, user_type=?, username=?, password=? WHERE id=?");
        $stmt->bind_param("sssssssssi", $title, $first_name, $middle_name, $last_name, $suffix, $academic_title, $user_type, $username, $hashed_password, $id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET title=?, first_name=?, middle_name=?, last_name=?, suffix=?, academic_title=?, user_type=?, username=? WHERE id=?");
        $stmt->bind_param("ssssssssi", $title, $first_name, $middle_name, $last_name, $suffix, $academic_title, $user_type, $username, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "User updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update user: " . $stmt->error;
    }

    header("Location: ../pages/users.php");
    exit;
}
?>
