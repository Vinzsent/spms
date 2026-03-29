<?php
$pageTitle = 'Under Construction';
include '../includes/auth.php';
include '../includes/db.php';

$dashboard_link = '../dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/dark-mode.css">
    <style>
        :root {
            --primary-green: #073b1d;
            --dark-green: #073b1d;
            --accent-orange: #EACA26;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .construction-container {
            text-align: center;
            background: #ffffff;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .construction-icon {
            font-size: 80px;
            color: var(--accent-orange);
            margin-bottom: 20px;
        }

        .construction-title {
            color: var(--primary-green);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .construction-text {
            color: #6c757d;
            font-size: 1.2rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-back {
            background-color: var(--primary-green);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back:hover {
            background-color: var(--dark-green);
            color: var(--accent-orange);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(7, 59, 29, 0.2);
        }

        /* Dark mode support */
        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #121416 0%, #0d1113 100%);
        }

        [data-bs-theme="dark"] .construction-container {
            background: #2c3034;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        [data-bs-theme="dark"] .construction-title {
            color: #ffffff;
        }

        [data-bs-theme="dark"] .construction-text {
            color: #adb5bd;
        }
    </style>
</head>
<body>
    <div class="construction-container">
        <i class="fas fa-tools construction-icon"></i>
        <h1 class="construction-title">Coming Soon</h1>
        <p class="construction-text">The maintenance page will available soon</p>
        <a href="<?= htmlspecialchars($dashboard_link) ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Return to Dashboard
        </a>
    </div>
</body>
</html>
