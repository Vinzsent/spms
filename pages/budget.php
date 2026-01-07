<?php
$pageTitle = 'Budget Overview';
include '../includes/auth.php';
include '../includes/db.php';

$user_type = $_SESSION['user_type'] ?? '';
$user_id = $_SESSION['user']['id'] ?? '';
$dashboard_link = '../dashboard.php';

// Get courses data
$course_query = "SELECT * FROM courses";
$course_result = mysqli_query($conn, $course_query);
$courses = [];
while ($row = mysqli_fetch_assoc($course_result)) {
    $courses[strtolower($row['course_name'])] = $row; // Use course_name as key instead of course_id
}

// Get budget data
$budget_query = "SELECT * FROM budget";
$budget_result = mysqli_query($conn, $budget_query);
$budget_data = [];
while ($row = mysqli_fetch_assoc($budget_result)) {
    $budget_data[strtolower($row['course_name'])] = $row;
    $budget_data['budget_max'] = $row['budget_max'];
}

// Get user's assigned course (for immediate heads)
$user_course = '';
if (strpos(strtolower($user_type), 'immediate head') !== false) {
    // Check if user_type has course suffix (like "Immediate Head - CELA")
    if (strpos(strtolower($user_type), 'immediate head - ') !== false) {
        // Extract course from user_type like "Immediate Head - CELA"
        $user_type_parts = explode(' - ', $user_type);
        if (count($user_type_parts) > 1) {
            $user_course = strtolower(trim(end($user_type_parts)));
        }
    } else {
        // For plain "Immediate Head", get course_id from users table
        $user_query = "SELECT course_id FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $user_course_id = strtolower($row['course_id']);
            // Now fetch course_name from courses table using course_id
            $course_name_query = "SELECT course_name FROM courses WHERE course_id = ?";
            $stmt_course = mysqli_prepare($conn, $course_name_query);
            mysqli_stmt_bind_param($stmt_course, "s", $user_course_id);
            mysqli_stmt_execute($stmt_course);
            $result_course = mysqli_stmt_get_result($stmt_course);
            if ($row_course = mysqli_fetch_assoc($result_course)) {
                $user_course = strtolower($row_course['course_name']);
            }
            mysqli_stmt_close($stmt_course);
        }
        mysqli_stmt_close($stmt);
    }
}

// Calculate spending for each course (placeholder - would need actual spending calculation)
function calculateSpent($course, $conn) {
    // This would typically sum up actual purchases/expenses for the course
    // For now, returning 0 as placeholder
    return 0;
}

function getCourseLogoPath($course_name, $course_id) {
    $name = strtolower($course_name);
    $id = strtolower($course_id);
    $candidates = [];
    $tokens = [
        'bsba' => 'BSBA-REVISED2.0.png',
        'cbm' => 'BSBA-REVISED2.0.png',
        'business' => 'BSBA-REVISED2.0.png',
        'business administration' => 'BSBA-REVISED2.0.png',
        'cela' => 'CELA-WEB.png',
        'education' => 'CELA-WEB.png',
        'liberal' => 'CELA-WEB.png',
        'cje' => 'CJE-WEB.png',
        'criminal' => 'CJE-WEB.png',
        'justice' => 'CJE-WEB.png',
        'hm' => 'HM LOGO-revised 2.0.png',
        'hme' => 'HM LOGO-revised 2.0.png',
        'hospitality' => 'HM LOGO-revised 2.0.png',
        'ite' => 'DCC ITE LOGO2.0.png',
        'information' => 'DCC ITE LOGO2.0.png',
        'technology' => 'DCC ITE LOGO2.0.png'
    ];
    foreach ($tokens as $token => $file) {
        if (strpos($name, $token) !== false || strpos($id, $token) !== false) {
            $candidates[] = $file;
        }
    }
    $candidates = array_unique($candidates);
    foreach ($candidates as $file) {
        $full = __DIR__ . '/../uploads/' . $file;
        if (file_exists($full)) {
            return '/spms/uploads/' . $file;
        }
    }
    $default = 'BSBA-REVISED2.0.png';
    $dfull = __DIR__ . '/../uploads/' . $default;
    if (file_exists($dfull)) {
        return '/spms/uploads/' . $default;
    }
    return null;
}

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

        /* Budget Card Styles */
        .budget-card {
            background: var(--text-white);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
        }

        .budget-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        .budget-card-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--text-white);
            padding: 20px 25px;
            position: relative;
        }

        .course-logo {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            height: 48px;
            width: auto;
            object-fit: contain;
            background: #ffffff;
            border-radius: 6px;
            padding: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 576px) {
            .course-logo {
                position: static;
                transform: none;
                display: block;
                margin-top: 10px;
            }
        }

        .budget-card-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .budget-card-body {
            padding: 30px;
        }

        .budget-overview {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 30px;
        }

        .budget-circle-container {
            text-align: center;
            min-width: 200px;
        }

        .budget-circle {
            --percent: 0;
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: conic-gradient(var(--accent-orange) calc(var(--percent) * 1%), #e9ecef 0);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
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
            font-size: 1.8rem;
        }

        .budget-percentage-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        .budget-details {
            flex: 1;
            min-width: 250px;
            text-align: center;
        }

        .budget-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .budget-detail-item:last-child {
            border-bottom: none;
        }

        .course-details-logo {
            display: block !important;
            margin: 0 auto 12px !important;
            height: 140px;
            width: 140px;
            object-fit: contain;
        }

        .budget-detail-label {
            font-weight: 500;
            color: #555;
        }

        .budget-detail-value {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1rem;
        }

        .budget-detail-value.positive {
            color: #28a745;
        }

        .budget-detail-value.negative {
            color: var(--accent-red);
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

        .alert-card.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-item {
            background: var(--text-white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.2rem;
            color: var(--text-white);
        }

        .stat-icon.budget {
            background-color: var(--primary-green);
        }

        .stat-icon.spent {
            background-color: var(--accent-orange);
        }

        .stat-icon.remaining {
            background-color: var(--accent-blue);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
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

            .budget-overview {
                flex-direction: column;
                text-align: center;
            }

            .budget-details {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Course Badge */
        .course-badge {
            display: inline-block;
            background: var(--accent-orange);
            color: var(--text-white);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        /* No Access Message */
        .no-access {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-access i {
            font-size: 4rem;
            color: var(--accent-red);
            margin-bottom: 20px;
        }

        .no-access h3 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
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
                <li><a href="budget.php" class="nav-link active">
                        <i class="fas fa-wallet"></i> Budget Overview
                    </a></li>
                <li><a href="issuance.php" class="nav-link">
                        <i class="fas fa-hand-holding"></i> Issuance
                    </a></li>
                <li><a href="notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i> notifications
                    </a></li>    
                <li><a href="../logout.php" class="nav-link logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <div class="content-header">
            <h1>Budget Overview</h1>
            <p>View budget information for all courses in the system</p>
        </div>

        

        <!-- Budget Overview for All Courses -->
        <?php if (strpos(strtolower($user_type), 'immediate head') !== false && $user_course): ?>
            <!-- Immediate Head View - Show only their course -->
            <?php
            $course_data = null;
            $budget_info = $budget_data[$user_course] ?? null;
            
            // Find course info from courses array
            $course_data = $courses[$user_course] ?? null;
            
            if ($course_data && $budget_info):
                $budget_max = floatval($budget_info['budget_max']);
                $spent = calculateSpent($user_course, $conn);
                $remaining = $budget_max - $spent;
                $percentage = $budget_max > 0 ? round(($remaining / $budget_max) * 100) : 0;
            ?>
            
            
            <div class="budget-card">
                <div class="budget-card-header">
                    <h3><?= htmlspecialchars($course_data['course_id']) ?> - Budget Overview</h3>
                    <?php $logoPath = getCourseLogoPath($course_data['course_name'] ?? '', $course_data['course_id'] ?? ''); if ($logoPath): ?>
                        <img class="course-logo" src="<?= $logoPath ?>" alt="<?= htmlspecialchars($course_data['course_name'] ?? '') ?> Logo">
                    <?php endif; ?>
                </div>
                <div class="budget-card-body">
                    <div class="course-badge"><?= htmlspecialchars($course_data['course_name']) ?></div>
                    
                    <div class="budget-overview">
                        <div class="budget-circle-container">
                            <div class="budget-circle" style="--percent: <?= $percentage ?>;">
                                <span><?= $percentage ?>%</span>
                            </div>
                            <div class="budget-percentage-label">Remaining Budget</div>
                        </div>
                        
                        <div class="budget-details">
                            <?php $logoPathDetails = getCourseLogoPath($course_data['course_name'] ?? '', $course_data['course_id'] ?? ''); if ($logoPathDetails): ?>
                                <img class="course-details-logo" src="<?= $logoPathDetails ?>" alt="<?= htmlspecialchars($course_data['course_name'] ?? '') ?> Logo">
                            <?php endif; ?>
                            <div class="budget-detail-item">
                                <span class="budget-detail-label">Course Code:</span>
                                <span class="budget-detail-value"><?= htmlspecialchars($course_data['course_id']) ?></span>
                            </div>
                            <div class="budget-detail-item">
                                <span class="budget-detail-label">Course Name:</span>
                                <span class="budget-detail-value"><?= htmlspecialchars($course_data['course_name']) ?></span>
                            </div>
                            <div class="budget-detail-item">
                                <span class="budget-detail-label">Total Budget:</span>
                                <span class="budget-detail-value">₱<?= number_format($budget_max, 2) ?></span>
                            </div>
                            <div class="budget-detail-item">
                                <span class="budget-detail-label">Spent Amount:</span>
                                <span class="budget-detail-value negative">₱<?= number_format($spent, 2) ?></span>
                            </div>
                            <div class="budget-detail-item">
                                <span class="budget-detail-label">Remaining Budget:</span>
                                <span class="budget-detail-value <?= $remaining >= 0 ? 'positive' : 'negative' ?>">
                                    ₱<?= number_format($remaining, 2) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-icon budget">
                                <i class="fas fa-piggy-bank"></i>
                            </div>
                            <div class="stat-value">₱<?= number_format($budget_max, 0) ?></div>
                            <div class="stat-label">Total Budget</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon spent">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-value">₱<?= number_format($spent, 0) ?></div>
                            <div class="stat-label">Spent Amount</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon remaining">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="stat-value">₱<?= number_format($remaining, 0) ?></div>
                            <div class="stat-label">Remaining</div>
                        </div>
                    </div>

                    <?php if ($remaining < 0): ?>
                    <div class="alert-card">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Budget Exceeded!</h5>
                        <p class="mb-0">Your course has exceeded the allocated budget by ₱<?= number_format(abs($remaining), 2) ?>. Please review expenses and consider budget reallocation.</p>
                    </div>
                    <?php elseif ($percentage < 20): ?>
                    <div class="alert-card warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Low Budget Warning</h5>
                        <p class="mb-0">Your course has only <?= $percentage ?>% of the budget remaining. Consider planning upcoming expenses carefully.</p>
                    </div>
                    <?php else: ?>
                    <div class="alert-card success">
                        <h5><i class="fas fa-check-circle me-2"></i>Budget Status Healthy</h5>
                        <p class="mb-0">Your course budget is well managed with <?= $percentage ?>% remaining. Keep up the good financial planning!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="no-access">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Course Budget Not Found</h3>
                <p>Your course budget information could not be found in the system. Please contact the MIS administrator.</p>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
        <div class="alert-card">
            <h5><i class="fas fa-info-circle me-2"></i>Budget Overview</h5>
            <p class="mb-0">View budget information for all courses in the system.</p>
        </div>
        
        <?php
        $total_budget = 0;
        $total_spent = 0;
        $total_remaining = 0;
        foreach ($courses as $course_key => $course) {
            $budget_info = $budget_data[$course_key] ?? null;
            if ($budget_info) {
                $budget = floatval($budget_info['budget_max']);
                $spent = calculateSpent($course_key, $conn);
                $remaining = $budget - $spent;
                $total_budget += $budget;
                $total_spent += $spent;
                $total_remaining += $remaining;
            }
        }
        ?>

        <div class="budget-card">
            <div class="budget-card-header">
                <h3>Overall Budget Summary</h3>
            </div>
            <div class="budget-card-body">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon spent">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-value">₱<?= number_format($total_budget, 0) ?></div>
                        <div class="stat-label">Total Budget</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon remaining">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value">₱<?= number_format($total_spent, 0) ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon remaining">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-value">₱<?= number_format($total_remaining, 0) ?></div>
                        <div class="stat-label">Total Remaining</div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php foreach ($courses as $course_key => $course): ?>
            <?php
            $budget_info = $budget_data[$course_key] ?? null;
            if ($budget_info) {
                $budget = floatval($budget_info['budget_max']);
                $budget_max = floatval($budget_info['budget_max']);
                $spent = calculateSpent($course_key, $conn);
                $remaining = $budget - $spent;
                $percentage = $budget > 0 ? round(($remaining / $budget) * 100) : 0;
            ?>
        
        <div class="budget-card">
            <div class="budget-card-header">
                <h3><?= htmlspecialchars($course['course_id']) ?> - Budget Overview</h3>
                <?php $logoPath = getCourseLogoPath($course['course_name'] ?? '', $course['course_id'] ?? ''); if ($logoPath): ?>
                    <img class="course-logo" src="<?= $logoPath ?>" alt="<?= htmlspecialchars($course['course_name'] ?? '') ?> Logo">
                <?php endif; ?>
            </div>
            <div class="budget-card-body">
                <div class="course-badge"><?= htmlspecialchars($course['course_name']) ?></div>
                
                <div class="budget-overview">
                    <div class="budget-circle-container">
                        <div class="budget-circle" style="--percent: <?= $percentage ?>;">
                            <span><?= $percentage ?>%</span>
                        </div>
                        <div class="budget-percentage-label">Remaining Budget</div>
                    </div>
                    
                    <div class="budget-details">
                        <?php $logoPathDetails = getCourseLogoPath($course['course_name'] ?? '', $course['course_id'] ?? ''); if ($logoPathDetails): ?>
                            <img class="course-details-logo" src="<?= $logoPathDetails ?>" alt="<?= htmlspecialchars($course['course_name'] ?? '') ?> Logo">
                        <?php endif; ?>
                        <div class="budget-detail-item">
                            <span class="budget-detail-label">Course Code:</span>
                            <span class="budget-detail-value"><?= htmlspecialchars($course['course_id']) ?></span>
                        </div>
                        <div class="budget-detail-item">
                            <span class="budget-detail-label">Course Name:</span>
                            <span class="budget-detail-value"><?= htmlspecialchars($course['course_name']) ?></span>
                        </div>
                        <div class="budget-detail-item">
                            <span class="budget-detail-label">Total Budget:</span>
                            <span class="budget-detail-value">₱<?= number_format($budget_max, 2) ?></span>
                        </div>
                        <div class="budget-detail-item">
                            <span class="budget-detail-label">Spent Amount:</span>
                            <span class="budget-detail-value negative">₱<?= number_format($spent, 2) ?></span>
                        </div>
                        <div class="budget-detail-item">
                            <span class="budget-detail-label">Remaining Budget:</span>
                            <span class="budget-detail-value <?= $remaining >= 0 ? 'positive' : 'negative' ?>">
                                ₱<?= number_format($remaining, 2) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon budget">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <div class="stat-value">₱<?= number_format($budget_max, 0) ?></div>
                        <div class="stat-label">Total Budget</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon spent">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-value">₱<?= number_format($spent, 0) ?></div>
                        <div class="stat-label">Spent Amount</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon remaining">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-value">₱<?= number_format($remaining, 0) ?></div>
                        <div class="stat-label">Remaining</div>
                    </div>
                </div>

                <?php if ($remaining < 0): ?>
                <div class="alert-card">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Budget Exceeded!</h5>
                    <p class="mb-0">This course has exceeded the allocated budget by ₱<?= number_format(abs($remaining), 2) ?>. Please review expenses and consider budget reallocation.</p>
                </div>
                <?php elseif ($percentage < 20): ?>
                <div class="alert-card warning">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Low Budget Warning</h5>
                    <p class="mb-0">This course has only <?= $percentage ?>% of the budget remaining. Consider planning upcoming expenses carefully.</p>
                </div>
                <?php else: ?>
                <div class="alert-card success">
                    <h5><i class="fas fa-check-circle me-2"></i>Budget Status Healthy</h5>
                    <p class="mb-0">This course budget is well managed with <?= $percentage ?>% remaining.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php 
        }
        endforeach; 
        ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }

        // Add mobile menu button if needed
        if (window.innerWidth <= 768) {
            const mobileBtn = document.createElement('button');
            mobileBtn.className = 'btn btn-primary mobile-menu-btn';
            mobileBtn.innerHTML = '<i class="fas fa-bars"></i>';
            mobileBtn.style.cssText = 'position: fixed; top: 20px; left: 20px; z-index: 1001;';
            mobileBtn.onclick = toggleSidebar;
            document.body.appendChild(mobileBtn);
        }

        // Animate budget circles on page load
        document.addEventListener('DOMContentLoaded', function() {
            const circles = document.querySelectorAll('.budget-circle');
            circles.forEach(circle => {
                const percent = circle.style.getPropertyValue('--percent');
                circle.style.opacity = '0';
                setTimeout(() => {
                    circle.style.transition = 'opacity 0.5s ease';
                    circle.style.opacity = '1';
                }, 100);
            });
        });
    </script>
</body>
</html>
