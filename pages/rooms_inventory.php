<?php

$pageTitle = 'Rooms Inventory';
include '../includes/auth.php';
include '../includes/db.php';

// Fetch all rooms inventory data
$query = "SELECT * FROM rooms_inventory ORDER BY building_name, floor, room_number";
$result = mysqli_query($conn, $query);
$rooms = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rooms[] = $row;
    }
}

// Group rooms by floor
$roomsByFloor = [];
foreach ($rooms as $room) {
    $floor = $room['floor'];
    if (!isset($roomsByFloor[$floor])) {
        $roomsByFloor[$floor] = [];
    }
    $roomsByFloor[$floor][] = $room;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Rooms Inventory</title>
</head>

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
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .stock-icons-btn i {
            font-weight: bold;
            margin: 0 2px;
        }
        .stock-icons-btn i:first-child { /* plus icon */
            color: #ffffff;
        }
        .stock-icons-btn i:last-child { /* minus icon */
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
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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

        /* Room Cards Styles */
        .floor-section {
            margin-bottom: 40px;
        }

        .floor-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--text-white);
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1.3rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .room-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent-orange);
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .room-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: white;
            padding: 12px 15px;
            border-radius: 6px;
            margin: -20px -20px 15px -20px;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
        }

        .room-building {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-top: 3px;
        }

        .inventory-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .inventory-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }

        .inventory-item:last-child {
            border-bottom: none;
        }

        .item-name {
            color: #555;
            font-weight: 500;
        }

        .item-quantity {
            background: var(--accent-orange);
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }

        .item-quantity.zero {
            background: #ccc;
        }

        .btn-add-room {
            background: linear-gradient(135deg, var(--accent-orange) 0%, #e55a2b 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-add-room:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
    </style>

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
            <li><a href="office_inventory.php" class="nav-link">
                    <i class="fas fa-building"></i> Office Inventory
                </a></li>
            <li><a href="property_inventory.php" class="nav-link">
                    <i class="fas fa-boxes"></i> Property Inventory
                </a></li>
            <li><a href="rooms_inventory.php" class="nav-link active">
                    <i class="fas fa-door-open"></i> Rooms Inventory
                </a></li>
            <li><a href="property_issuance.php" class="nav-link">
                    <i class="fas fa-hand-holding"></i> Property Issuance
                </a></li>
            <li><a href="equipment_transfer_request.php" class="nav-link">
                    <i class="fas fa-exchange-alt"></i> Transfer Request
                </a></li>
            <li><a href="borrowers_forms.php" class="nav-link">
                    <i class="fas fa-hand-holding"></i> Borrower Forms
                </a></li>
            <li><a href="aircon_list.php" class="nav-link">
                    <i class="fas fa-snowflake"></i> Aircons
                </a></li>
            <li><a href="../logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a></li>
                    </a></li>
            </ul>
        </nav>
    </div>


<!-- Main Content -->
    <main class="main-content">
        <div class="content-header">
            <h1><i class="fas fa-door-open"></i> Rooms Inventory</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Main Campus</p>
        </div>

        <div class="page-header">
            <h2 style="margin: 0; color: var(--text-dark);">All Rooms</h2>
            <button class="btn-add-room">
                <i class="fas fa-plus"></i> Add New Room
            </button>
        </div>

        <?php
        $floorNames = [
            'R' => 'First Floor (R)',
            'N' => 'Second Floor (N)',
            'O' => 'Third Floor (O)'
        ];

        foreach ($roomsByFloor as $floor => $floorRooms):
        ?>
        <div class="floor-section">
            <div class="floor-header">
                <i class="fas fa-layer-group"></i> <?= $floorNames[$floor] ?? "Floor $floor" ?>
            </div>

            <div class="rooms-grid">
                <?php foreach ($floorRooms as $room): ?>
                <div class="room-card">
                    <div class="room-header">
                        <?= htmlspecialchars($room['building_name']) ?>
                        <div class="room-building">Room <?= htmlspecialchars($room['room_number']) ?></div>
                    </div>

                    <ul class="inventory-list">
                        <li class="inventory-item">
                            <span class="item-name">Fluorescent light</span>
                            <span class="item-quantity <?= $room['fluorescent_light'] == 0 ? 'zero' : '' ?>">
                                <?= $room['fluorescent_light'] ?>
                            </span>
                        </li>
                        <li class="inventory-item">
                            <span class="item-name">Electric Fans- Wall</span>
                            <span class="item-quantity <?= $room['electric_fans_wall'] == 0 ? 'zero' : '' ?>">
                                <?= $room['electric_fans_wall'] ?>
                            </span>
                        </li>
                        <li class="inventory-item">
                            <span class="item-name">Ceiling</span>
                            <span class="item-quantity <?= $room['ceiling'] == 0 ? 'zero' : '' ?>">
                                <?= $room['ceiling'] ?>
                            </span>
                        </li>
                        <li class="inventory-item">
                            <span class="item-name">Chairs - Mono</span>
                            <span class="item-quantity <?= $room['chairs_mono'] == 0 ? 'zero' : '' ?>">
                                <?= $room['chairs_mono'] ?>
                            </span>
                        </li>
                        <li class="inventory-item">
                            <span class="item-name">Steel</span>
                            <span class="item-quantity <?= $room['steel'] == 0 ? 'zero' : '' ?>">
                                <?= $room['steel'] ?>
                            </span>
                        </li>
                        <li class="inventory-item">
                            <span class="item-name">Plastic (mini)</span>
                            <span class="item-quantity <?= $room['plastic_mini'] == 0 ? 'zero' : '' ?>">
                                <?= $room['plastic_mini'] ?>
                            </span>
                        </li>
                        <li class="inventory-item">
                            <span class="item-name">Teacher's Table</span>
                            <span class="item-quantity <?= $room['teacher_table'] == 0 ? 'zero' : '' ?>">
                                <?= $room['teacher_table'] ?>
                            </span>
                        </li>
                        <li class="inventory-item">
                            <span class="item-name">Black/Whiteboard</span>
                            <span class="item-quantity <?= $room['black_whiteboard'] == 0 ? 'zero' : '' ?>">
                                <?= $room['black_whiteboard'] ?>
                            </span>
                        </li>
                        <li class="inventory-item">
                            <span class="item-name">Platform</span>
                            <span class="item-quantity <?= $room['platform'] == 0 ? 'zero' : '' ?>">
                                <?= $room['platform'] ?>
                            </span>
                        </li>
                        <li class="inventory-item">
                            <span class="item-name">TV</span>
                            <span class="item-quantity <?= $room['tv'] == 0 ? 'zero' : '' ?>">
                                <?= $room['tv'] ?>
                            </span>
                        </li>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($rooms)): ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
            <i class="fas fa-door-open" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="color: #666;">No rooms found</h3>
            <p style="color: #999;">Start by adding your first room to the inventory.</p>
        </div>
        <?php endif; ?>
    </main>


    
</body>
</html>