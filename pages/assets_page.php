<?php

$pageTitle = 'Budget';
include '../includes/auth.php';
include '../includes/db.php';

// Handle AJAX requests for pagination
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    // This is an AJAX request, return only the table content
    $isAjax = true;
} else {
    include '../includes/header.php';
    $isAjax = false;
}

$user_type = $_SESSION['user_type'] ?? '';
$dashboard_link = ($user_type == 'Admin') ? '../dashboard.php' : '../dashboard.php';


$course_query = "SELECT * FROM courses";
$course_result = mysqli_query($conn, $course_query);
$courses = [];
while ($row = mysqli_fetch_assoc($course_result)) {
    $courses[strtolower($row['course_id'])] = $row;
}


$budget_query = "SELECT * FROM budget";
$budget_result = mysqli_query($conn, $budget_query);
$budget_data = [];
while ($row = mysqli_fetch_assoc($budget_result)) {
    $budget_data[strtolower($row['course_name'])] = $row;
    $budget_data['budget_max'] = $row['budget_max'];
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget</title>
</head>

<body>

 <style>
        html {
            scroll-behavior: smooth;
        }

        :root {
            --primary-green: #073b1d;
            --dark-green: #073b1d;
            --light-green: #2d8aad;
            --accent-orange: #EACA26;
            --accent-blue: #4a90e2;
            --accent-red: #e74c3c;
            --accent-yellow: #f39c12;
            --text-white: #ffffff;
            --text-dark: #073b1d;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
        }

        .stock-icons-btn {
            background: linear-gradient(to right, #28a745 50%, #ffc107 50%);
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .stock-icons-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .stock-icons-btn i {
            font-weight: bold;
            margin: 0 2px;
        }

        .stock-icons-btn i:first-child {
            /* plus icon */
            color: #ffffff;
        }

        .stock-icons-btn i:last-child {
            /* minus icon */
            color: #000000;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--text-white);
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-white);
        }

        .welcome-text {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            padding: 0;
            margin: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: var(--text-white);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--text-white);
            border-left-color: var(--accent-orange);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left-color: var(--accent-orange);
            font-weight: 600;
        }

        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .nav-link.logout {
            color: var(--accent-red);
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            min-height: 100vh;
            background-color: var(--bg-light);
        }

        .content-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--text-white);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .content-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2.2rem;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--text-white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: var(--text-white);
        }

        .stat-icon.items {
            background-color: var(--primary-green);
        }

        .stat-icon.low-stock {
            background-color: var(--accent-yellow);
        }

        .stat-icon.out-of-stock {
            background-color: var(--accent-red);
        }

        .stat-icon.movements {
            background-color: var(--accent-blue);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Alert Styles */
        .alert-card {
            background: linear-gradient(135deg, var(--accent-red) 0%, #c0392b 100%);
            color: var(--text-white);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-card.warning {
            background: linear-gradient(135deg, var(--accent-yellow) 0%, #e67e22 100%);
        }

        /* Session Alert Styles */
        .alert {
            margin-bottom: 20px;
            margin-left: 0;
            margin-right: 0;
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-left: 4px solid #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-left: 4px solid #721c24;
        }

        .alert .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .alert .btn-close:hover {
            opacity: 1;
        }

        .alert .flex-grow-1 {
            min-width: 0;
            word-break: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            max-width: calc(100% - 60px);
            padding-right: 10px;
        }

        /* Ensure alerts have proper spacing from sidebar on larger screens */
        @media (min-width: 769px) {
            .alert {
                margin-left: 0;
                margin-right: 0;
                padding-left: 20px;
                padding-right: 20px;
            }

            .alert .flex-grow-1 {
                max-width: calc(100% - 80px);
                padding-right: 15px;
            }
        }

        @media (max-width: 768px) {
            .alert {
                font-size: 0.9rem;
                padding: 12px 15px;
            }

            .alert .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .alert .btn-close {
                align-self: flex-end;
                margin-top: -10px;
                margin-right: -10px;
            }
        }

        /* Table Styles */
        .table-container {
            background: var(--text-white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--text-white);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .btn-add {
            background-color: var(--accent-orange);
            border: none;
            color: var(--text-white);
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            background-color: #e55a2b;
            transform: translateY(-2px);
        }

        /* Stock Level Indicators */
        .stock-level {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-level.critical {
            background-color: var(--accent-red);
            color: var(--text-white);
        }

        .stock-level.low {
            background-color: var(--accent-yellow);
            color: var(--text-dark);
        }

        .stock-level.normal {
            background-color: var(--accent-blue);
            color: var(--text-white);
        }

        .stock-level.out {
            background-color: #6c757d;
            color: var(--text-white);
        }

        /* Movement Button Styles */
        .movement-btn {
            transition: all 0.3s ease;
            border-width: 2px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .movement-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .movement-btn.active {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            border-width: 3px;
        }

        .movement-btn.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 0.3;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 0.3;
            }
        }

        .movement-btn.btn-outline-success {
            border-color: #198754;
            color: #198754;
            background-color: rgba(25, 135, 84, 0.1);
        }

        .movement-btn.btn-outline-warning {
            border-color: #ffc107;
            color: #856404;
            background-color: rgba(255, 193, 7, 0.1);
        }

        /* Search Input Styles */
        .search-input {
            min-width: 200px;
        }

        .search-input input {
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .search-input input:focus {
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }

        /* Loading indicator for search input */
        .search-input input.loading {
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            animation: spin 1s linear infinite;
            padding-right: 35px;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .btn-search {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .btn-search:hover {
            background-color: #357abd;
            border-color: #357abd;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .alert {
                margin-left: 10px;
                margin-right: 10px;
                font-size: 0.9rem;
                padding: 12px 15px;
            }

            .alert .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .alert .btn-close {
                align-self: flex-end;
                margin-top: -10px;
                margin-right: -10px;
            }

            .alert .flex-grow-1 {
                max-width: 100%;
                margin-right: 30px;
            }
        }

        .course-logos-section {
            margin-top: 30px;
        }

        .course-logos-row {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 20px;
            align-items: center;
            justify-items: center;
            padding: 20px;
        }

        .course-logo-card {
            background: var(--text-white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .course-logo-card img {
            max-width: 100%;
            max-height: 120px;
            object-fit: contain;
            display: block;
        }

        .course-logo-card.active {
            border: 3px solid var(--accent-orange);
            transform: translateY(-3px);
        }

        .course-tabs-content {
            background: var(--text-white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 0 20px 20px;
        }

        .course-tab-pane {
            display: none;
        }

        .course-tab-pane.active {
            display: block;
        }

        .budget-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            padding: 10px 0 20px;
            flex-wrap: wrap;
        }

        .budget-indicator-main {
            text-align: center;
        }

        .budget-circle {
            --percent: 0;
            position: relative;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: conic-gradient(var(--accent-orange) calc(var(--percent) * 1%), #e9ecef 0);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .budget-circle::before {
            content: '';
            position: absolute;
            width: 70%;
            height: 70%;
            border-radius: 50%;
            background: #ffffff;
        }

        .budget-circle span {
            position: relative;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.5rem;
        }

        .budget-indicator-text h4 {
            margin-bottom: 0;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .budget-details {
            max-width: 320px;
        }

        .budget-details p {
            margin-bottom: 0.25rem;
            color: #555555;
        }

        .budget-details p:last-child {
            margin-bottom: 0;
        }

        .budget-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;
        }

        @media (max-width: 992px) {
            .course-logos-row {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }
        }
    </style>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>DARTS</h3>
            <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></div>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav-item">
                <li><a href="<?= $dashboard_link ?>" class="nav-link">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a></li>
                <li><a href="assets.php" class="nav-link active">
                        <i class="fas fa-wallet"></i> Budgets
                    </a></li>
                <li><a href="assets.php" class="nav-link">
                        <i class="fas fa-boxes"></i> Assets
                    </a></li>
                <li><a href="../logout.php" class="nav-link logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <div class="content-header">
            <h1 class="mb-3">Budgets &amp; Assets by Course</h1>
            <p>Click the course to see the budget information</p>
        </div>

        <div class="table-container course-logos-section">
            <div class="table-header">
                <h3 class="mb-3">Courses</h3>
            </div>
            <div class="course-logos-row">
                <div class="course-logo-card active" data-course="bsba">
                    <img src="../uploads/BSBA-REVISED2.0.png" alt="BSBA Logo">
                    <p>College of Business Administration</p>
                </div>
                <div class="course-logo-card" data-course="cela">
                    <img src="../uploads/CELA-WEB.png" alt="CELA Logo">
                    <p>College of Education and Liberal Arts</p>
                </div>
                <div class="course-logo-card" data-course="cje">
                    <img src="../uploads/CJE-WEB.png" alt="CJE Logo">
                    <p>Criminal Justice Education</p>
                </div>
                <div class="course-logo-card" data-course="hm">
                    <img src="../uploads/HM LOGO-revised 2.0.png" alt="HM Logo">
                    <p>Hospitality Management Education</p>
                </div>
                <div class="course-logo-card" data-course="ite">
                    <img src="../uploads/DCC ITE LOGO2.0.png" alt="ITE Logo">
                    <p>Information Technology Education</p>
                </div>
            </div>
        </div>


        <!--Budget Information-->
        <div class="course-tabs-content">
            <div class="course-tab-pane active" data-course="bsba">
                <div class="budget-indicator">
                    <div class="budget-indicator-main">
                        <div class="budget-indicator-text">
                            <h4>REMAINING BUDGET</h4>
                        </div>
                        <?php 
                        $overall_budget = isset($budget_data['bsba']['budget']) ? floatval($budget_data['bsba']['budget']) : 0;
                        $spent = 0; // This would be calculated from actual spending data
                        $remaining = $overall_budget - $spent;
                        $percentage = $overall_budget > 0 ? round(($remaining / $overall_budget) * 100) : 0;
                        ?>
                        <div class="budget-circle" style="--percent: <?php echo $percentage; ?>;">
                            <span><?php echo $percentage; ?>%</span>
                        </div>
                    </div>
                    <div class="budget-details">
                        <p><strong>Course:</strong> <?php echo isset($courses['bsba']['course_id']) ? htmlspecialchars($courses['bsba']['course_id']) : 'CBM'; ?></p>
                        <p><strong>Overall budget:</strong> <?php echo isset($budget_data['bsba']['budget_max']) ? number_format($budget_data['bsba']['budget_max'], 2) : '0.00'; ?></p>
                        <p><strong>Spent so far:</strong> 0.00</p>
                        <p><strong>Remaining budget:</strong> <?php echo isset($budget_data['bsba']['budget']) ? number_format($budget_data['bsba']['budget'], 2) : '0.00'; ?></p>
                        <div class="budget-actions">
                            <button type="button" class="btn btn-sm btn-primary btn-add-budget" data-course="bsba" data-bs-toggle="modal" data-bs-target="#addBudgetModal">Add</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="course-tab-pane" data-course="cela">
                <div class="budget-indicator">
                    <div class="budget-indicator-main">
                        <div class="budget-indicator-text">
                            <h4>REMAINING BUDGET</h4>
                        </div>
                        <?php 
                        $overall_budget_cela = isset($budget_data['cela']['budget']) ? floatval($budget_data['cela']['budget']) : 0;
                        $spent_cela = 0; // This would be calculated from actual spending data
                        $remaining_cela = $overall_budget_cela - $spent_cela;
                        $percentage_cela = $overall_budget_cela > 0 ? round(($remaining_cela / $overall_budget_cela) * 100) : 0;
                        ?>
                        <div class="budget-circle" style="--percent: <?php echo $percentage_cela; ?>;">
                            <span><?php echo $percentage_cela; ?>%</span>
                        </div>
                    </div>
                    <div class="budget-details">
                        <p><strong>Course:</strong> <?php echo isset($courses['cela']['course_id']) ? htmlspecialchars($courses['cela']['course_id']) : 'CELA'; ?></p>
                        <p><strong>Overall budget:</strong> <?php echo isset($budget_data['cela']['budget_max']) ? number_format($budget_data['cela']['budget_max'], 2) : '0.00'; ?></p>
                        <p><strong>Spent so far:</strong> 0.00</p>
                        <p><strong>Remaining budget:</strong> <?php echo isset($budget_data['cela']['budget']) ? number_format($budget_data['cela']['budget'], 2) : '0.00'; ?></p>
                        <div class="budget-actions">
                            <button type="button" class="btn btn-sm btn-primary btn-add-budget" data-course="cela" data-bs-toggle="modal" data-bs-target="#addBudgetModal">Add</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="course-tab-pane" data-course="cje">
                <div class="budget-indicator">
                    <div class="budget-indicator-main">
                        <div class="budget-indicator-text">
                            <h4>REMAINING BUDGET</h4>
                        </div>
                        <?php 
                        $overall_budget_cje = isset($budget_data['cje']['budget']) ? floatval($budget_data['cje']['budget']) : 0;
                        $spent_cje = 0; // This would be calculated from actual spending data
                        $remaining_cje = $overall_budget_cje - $spent_cje;
                        $percentage_cje = $overall_budget_cje > 0 ? round(($remaining_cje / $overall_budget_cje) * 100) : 0;
                        ?>
                        <div class="budget-circle" style="--percent: <?php echo $percentage_cje; ?>;">
                            <span><?php echo $percentage_cje; ?>%</span>
                        </div>
                    </div>
                    <div class="budget-details">
                        <p><strong>Course:</strong> <?php echo isset($courses['cje']['course_id']) ? htmlspecialchars($courses['cje']['course_id']) : 'CJE'; ?></p>
                        <p><strong>Overall budget:</strong> <?php echo isset($budget_data['cje']['budget_max']) ? number_format($budget_data['cje']['budget_max'], 2) : '0.00'; ?></p>
                        <p><strong>Spent so far:</strong> 0.00</p>
                        <p><strong>Remaining budget:</strong> <?php echo isset($budget_data['cje']['budget']) ? number_format($budget_data['cje']['budget'], 2) : '0.00'; ?></p>
                        <div class="budget-actions">
                            <button type="button" class="btn btn-sm btn-primary btn-add-budget" data-course="cje" data-bs-toggle="modal" data-bs-target="#addBudgetModal">Add</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="course-tab-pane" data-course="hm">
                <div class="budget-indicator">
                    <div class="budget-indicator-main">
                        <div class="budget-indicator-text">
                            <h4>REMAINING BUDGET</h4>
                        </div>
                        <?php 
                        $overall_budget_hm = isset($budget_data['hm']['budget']) ? floatval($budget_data['hm']['budget']) : 0;
                        $spent_hm = 0; // This would be calculated from actual spending data
                        $remaining_hm = $overall_budget_hm - $spent_hm;
                        $percentage_hm = $overall_budget_hm > 0 ? round(($remaining_hm / $overall_budget_hm) * 100) : 0;
                        ?>
                        <div class="budget-circle" style="--percent: <?php echo $percentage_hm; ?>;">
                            <span><?php echo $percentage_hm; ?>%</span>
                        </div>
                    </div>
                    <div class="budget-details">
                        <p><strong>Course:</strong> <?php echo isset($courses['hm']['course_id']) ? htmlspecialchars($courses['hm']['course_id']) : 'HME'; ?></p>
                        <p><strong>Overall budget:</strong> <?php echo isset($budget_data['hm']['budget_max']) ? number_format($budget_data['hm']['budget_max'], 2) : '0.00'; ?></p>
                        <p><strong>Spent so far:</strong> 0.00</p>
                        <p><strong>Remaining budget:</strong> <?php echo isset($budget_data['hm']['budget']) ? number_format($budget_data['hm']['budget'], 2) : '0.00'; ?></p>
                        <div class="budget-actions">
                            <button type="button" class="btn btn-sm btn-primary btn-add-budget" data-course="hm" data-bs-toggle="modal" data-bs-target="#addBudgetModal">Add</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="course-tab-pane" data-course="ite">
                <div class="budget-indicator">
                    <div class="budget-indicator-main">
                        <div class="budget-indicator-text">
                            <h4>REMAINING BUDGET</h4>
                        </div>
                        <?php 
                        $overall_budget_ite = isset($budget_data['ite']['budget']) ? floatval($budget_data['ite']['budget']) : 0;
                        $spent_ite = 0; // This would be calculated from actual spending data
                        $remaining_ite = $overall_budget_ite - $spent_ite;
                        $percentage_ite = $overall_budget_ite > 0 ? round(($remaining_ite / $overall_budget_ite) * 100) : 0;
                        ?>
                        <div class="budget-circle" style="--percent: <?php echo $percentage_ite; ?>;">
                            <span><?php echo $percentage_ite; ?>%</span>
                        </div>
                    </div>
                    <div class="budget-details">
                        <p><strong>Course:</strong> <?php echo isset($courses['ite']['course_id']) ? htmlspecialchars($courses['ite']['course_id']) : 'ITE'; ?></p>
                        <p><strong>Overall budget:</strong> <?php echo isset($budget_data['ite']['budget_max']) ? number_format($budget_data['ite']['budget_max'], 2) : '0.00'; ?></p>
                        <p><strong>Spent so far:</strong> 0.00</p>
                        <p><strong>Remaining budget:</strong> <?php echo isset($budget_data['ite']['budget']) ? number_format($budget_data['ite']['budget'], 2) : '0.00'; ?></p>
                        <div class="budget-actions">
                            <button type="button" class="btn btn-sm btn-primary btn-add-budget" data-course="ite" data-bs-toggle="modal" data-bs-target="#addBudgetModal">Add</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-course="ite" data-bs-toggle="modal" data-bs-target="#editBudgetModal">Edit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container mt-3">
            <div class="table-header">
                <h3 class="mb-3">Request Items History</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">Date</th>
                            <th class="text-center">Item Name</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Unit</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No request items yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Budget Modal -->
    <div class="modal fade" id="addBudgetModal" tabindex="-1" aria-labelledby="addBudgetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="../actions/assets_page.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBudgetModalLabel">Add Budget for</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="course_id_display" class="form-label">Course</label>
                            <input type="text" class="form-control" name="course_name" id="course_id_display" readonly style="background-color: #f8f9fa; font-weight: bold;">
                        </div>
                        <div class="mb-3">
                            <label for="budget_max" class="form-label">Maximum Budget</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="budget_max" name="budget_max" required>
                        </div>
                        <input type="hidden" id="budget_course" name="course_code">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_budget" class="btn btn-primary">Save Budget</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Budget Modal -->
    <div class="modal fade" id="editBudgetModal" tabindex="-1" aria-labelledby="editBudgetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="../actions/edit_assets_page.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBudgetModalLabel">Edit Budget for</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="course_id_display" class="form-label">Course</label>
                            <input type="text" class="form-control" name="course_name" id="course_id_display" readonly style="background-color: #f8f9fa; font-weight: bold;">
                        </div>
                        <div class="mb-3">
                            <label for="budget_max" class="form-label">Maximum Budget</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="budget_max" name="budget_max" required>
                        </div>
                        <input type="hidden" id="budget_course" name="course_code">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_budget" class="btn btn-primary">Save Budget</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Bootstrap tooltips and popovers
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            const cards = document.querySelectorAll('.course-logo-card');
            const panes = document.querySelectorAll('.course-tab-pane');
            const addButtons = document.querySelectorAll('.btn-add-budget');
            const editButtons = document.querySelectorAll('[data-bs-target="#editBudgetModal"]');
            const budgetCourseInput = document.getElementById('budget_course');
            const courseIdDisplay = document.getElementById('course_id_display');

            // Initialize modals
            const addBudgetModal = new bootstrap.Modal(document.getElementById('addBudgetModal'));
            const editBudgetModal = new bootstrap.Modal(document.getElementById('editBudgetModal'));

            cards.forEach(function (card) {
                card.addEventListener('click', function () {
                    const target = this.getAttribute('data-course');
                    if (!target) return;

                    cards.forEach(function (c) {
                        c.classList.remove('active');
                    });
                    this.classList.add('active');

                    panes.forEach(function (pane) {
                        if (pane.getAttribute('data-course') === target) {
                            pane.classList.add('active');
                        } else {
                            pane.classList.remove('active');
                        }
                    });
                });
            });

            addButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const course = this.getAttribute('data-course') || '';
                    if (budgetCourseInput && courseIdDisplay) {
                        budgetCourseInput.value = course;
                        courseIdDisplay.value = course.toUpperCase(); // Display course ID in uppercase
                        // Show the modal after setting the course
                        addBudgetModal.show();
                    }
                });
            });

            // Add event listeners for edit buttons
            editButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const course = this.getAttribute('data-course') || '';
                    if (budgetCourseInput && courseIdDisplay) {
                        budgetCourseInput.value = course;
                        courseIdDisplay.value = course.toUpperCase(); // Display course ID in uppercase
                        // Show the edit modal after setting the course
                        editBudgetModal.show();
                    }
                });
            });
        });
    </script>

</body>
</html>