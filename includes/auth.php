<?php
session_start();
if (!isset($_SESSION['user'])) {
    // Dynamically find the path to index.php at the root
    $rootPath = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../index.php' : 'index.php';
    header("Location: $rootPath");
    exit;
}
