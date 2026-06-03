<?php
$pageTitle = 'Reports';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Access control: strictly Supply In-charge, Property Custodian, and Admin
$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));

// Normalized allowed roles
$allowed_roles = ['supplyincharge', 'propertycustodian', 'admin'];

if (!in_array($user_type, $allowed_roles)) {
    $_SESSION['error'] = 'Access denied. You do not have permission to view the Reports page.';
    header("Location: ../dashboard.php");
    exit;
}

$dashboard_link = '../dashboard.php';
?>

<style>
    :root {
        --primary-green: #073b1d;
        --dark-green: #073b1d;
        --light-green: #2d8aad;
        --accent-orange: #EACA26;
        --text-white: #ffffff;
        --bg-light: #f8f9fa;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--bg-light);
    }

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: 240px;
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
    }

    .sidebar-nav {
        padding: 20px 0;
    }

    .nav-item {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: var(--text-white);
        text-decoration: none;
        transition: all 0.3s;
        border-left: 4px solid transparent;
        font-size: 0.9rem;
    }

    .nav-link:hover,
    .nav-link.active {
        background: rgba(255, 255, 255, 0.1);
        border-left-color: var(--accent-orange);
    }

    .nav-link i {
        margin-right: 15px;
        width: 20px;
        text-align: center;
    }

    .main-content {
        margin-left: 280px;
        padding: 30px;
    }

    .content-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .report-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border-top: 5px solid var(--primary-green);
        transition: transform 0.3s;
    }

    .report-card:hover {
        transform: translateY(-5px);
    }

    .report-card i {
        font-size: 2rem;
        color: var(--primary-green);
        margin-bottom: 15px;
    }

    .btn-generate {
        background: var(--primary-green);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        margin-top: 15px;
    }
    .btn-generate:hover {
        background: var(--dark-green);
    }
</style>

<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="content-header">
        <h1>Reports & Analytics</h1>
        <p>Generate and download comprehensive system reports</p>
    </div>

    <div class="report-grid">
        <div class="report-card">
            <i class="fas fa-boxes"></i>
            <h3>Inventory Report</h3>
            <p>Summary of all property items, stock levels, and valuations.</p>
            <button class="btn-generate">Generate PDF</button>
        </div>

        <div class="report-card">
            <i class="fas fa-tools"></i>
            <h3>Maintenance Logs</h3>
            <p>History of all repairs and maintenance activities for property units.</p>
            <button class="btn-generate">Download CSV</button>
        </div>

        <div class="report-card">
            <i class="fas fa-exchange-alt"></i>
            <h3>Issuance Records</h3>
            <p>Records of all items issued to departments and individuals.</p>
            <button class="btn-generate">Generate PDF</button>
        </div>

        <div class="report-card">
            <i class="fas fa-trash-alt"></i>
            <h3>Disposal Summary</h3>
            <p>Log of decommissioned items and disposal reasons.</p>
            <button class="btn-generate">Download CSV</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>