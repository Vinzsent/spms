<?php
$pageTitle = 'Procurement Statistics';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';

// Get statistics data
// 1. Supply Requests Statistics
$total_requests_sql = "SELECT COUNT(*) as count FROM supply_request";
$total_requests = $conn->query($total_requests_sql)->fetch_assoc()['count'];

$approved_requests_sql = "SELECT COUNT(*) as count FROM supply_request WHERE approved_by IS NOT NULL";
$approved_requests = $conn->query($approved_requests_sql)->fetch_assoc()['count'];

$pending_requests_sql = "SELECT COUNT(*) as count FROM supply_request WHERE approved_by IS NULL";
$pending_requests = $conn->query($pending_requests_sql)->fetch_assoc()['count'];

// 2. Inventory Statistics
$total_inventory_sql = "SELECT COUNT(*) as count, SUM(current_stock) as total_qty FROM inventory";
$inventory_stats = $conn->query($total_inventory_sql)->fetch_assoc();
$total_inventory_items = $inventory_stats['count'];
$total_inventory_qty = $inventory_stats['total_qty'] ?? 0;

$low_stock_inventory_sql = "SELECT COUNT(*) as count FROM inventory WHERE current_stock <= reorder_level";
$low_stock_inventory = $conn->query($low_stock_inventory_sql)->fetch_assoc()['count'];

// 3. Property Inventory Statistics
$total_property_sql = "SELECT COUNT(*) as count, SUM(current_stock) as total_qty FROM property_inventory";
$property_stats = $conn->query($total_property_sql)->fetch_assoc();
$total_property_items = $property_stats['count'];
$total_property_qty = $property_stats['total_qty'] ?? 0;

$low_stock_property_sql = "SELECT COUNT(*) as count FROM property_inventory WHERE current_stock <= reorder_level";
$low_stock_property = $conn->query($low_stock_property_sql)->fetch_assoc()['count'];

// 4. Request Type Distribution
$consumables_requests_sql = "SELECT COUNT(*) as count FROM supply_request WHERE LOWER(request_type) = 'consumables'";
$consumables_requests = $conn->query($consumables_requests_sql)->fetch_assoc()['count'];

$property_requests_sql = "SELECT COUNT(*) as count FROM supply_request WHERE LOWER(request_type) = 'property'";
$property_requests = $conn->query($property_requests_sql)->fetch_assoc()['count'];

// 5. Monthly Request Trends (Last 12 months)
$monthly_requests_sql = "SELECT 
    DATE_FORMAT(date_requested, '%Y-%m') as month,
    COUNT(*) as count
    FROM supply_request
    WHERE date_requested >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(date_requested, '%Y-%m')
    ORDER BY month ASC";
$monthly_requests_result = $conn->query($monthly_requests_sql);
$monthly_data = [];
while ($row = $monthly_requests_result->fetch_assoc()) {
    $monthly_data[] = $row;
}

// 6. Top Requested Items
$top_items_sql = "SELECT 
    item_name,
    COUNT(*) as request_count,
    SUM(quantity_requested) as quantity
    FROM supply_request
    GROUP BY item_name
    ORDER BY request_count DESC
    LIMIT 10";
$top_items_result = $conn->query($top_items_sql);
$top_items = [];
while ($row = $top_items_result->fetch_assoc()) {
    $top_items[] = $row;
}

// 7. Inventory Status Distribution
$inventory_status_sql = "SELECT 
    status,
    COUNT(*) as count
    FROM inventory
    GROUP BY status";
$inventory_status_result = $conn->query($inventory_status_sql);
$inventory_status = [];
while ($row = $inventory_status_result->fetch_assoc()) {
    $inventory_status[] = $row;
}

// 8. Property Inventory Status Distribution
$property_status_sql = "SELECT 
    status,
    COUNT(*) as count
    FROM property_inventory
    GROUP BY status";
$property_status_result = $conn->query($property_status_sql);
$property_status = [];
while ($row = $property_status_result->fetch_assoc()) {
    $property_status[] = $row;
}

// 9. Request Status Workflow
$noted_requests_sql = "SELECT COUNT(*) as count FROM supply_request WHERE noted_by IS NOT NULL";
$noted_requests = $conn->query($noted_requests_sql)->fetch_assoc()['count'];

$checked_requests_sql = "SELECT COUNT(*) as count FROM supply_request WHERE checked_by IS NOT NULL";
$checked_requests = $conn->query($checked_requests_sql)->fetch_assoc()['count'];

$verified_requests_sql = "SELECT COUNT(*) as count FROM supply_request WHERE verified_by IS NOT NULL";
$verified_requests = $conn->query($verified_requests_sql)->fetch_assoc()['count'];

$issued_requests_sql = "SELECT COUNT(*) as count FROM supply_request WHERE issued_by IS NOT NULL";
$issued_requests = $conn->query($issued_requests_sql)->fetch_assoc()['count'];

// 10. Category-wise Inventory Distribution
$category_inventory_sql = "SELECT 
    category,
    COUNT(*) as count,
    SUM(current_stock) as total_stock
    FROM inventory
    GROUP BY category
    ORDER BY count DESC
    LIMIT 10";
$category_inventory_result = $conn->query($category_inventory_sql);
$category_inventory = [];
while ($row = $category_inventory_result->fetch_assoc()) {
    $category_inventory[] = $row;
}
?>

<style>
    :root {
        --primary-green: #073b1d;
        --dark-green: #073b1d;
        --light-green: #2d8aad;
        --accent-orange: #EACA26;
        --accent-blue: #4a90e2;
        --accent-green-approved: #28a745;
        --accent-red: #e74c3c;
        --accent-purple: #9b59b6;
        --accent-teal: #1abc9c;
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

    /* Main Content - No Sidebar */
    .main-content {
        margin-left: 0;
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
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .content-header h1 {
        margin: 0;
        font-weight: 700;
        font-size: 2.2rem;
    }

    .back-btn {
        background-color: var(--accent-orange);
        border: none;
        color: var(--text-white);
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .back-btn:hover {
        background-color: #e55a2b;
        transform: translateY(-2px);
        color: var(--text-white);
    }

    /* Stats Cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
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

    .stat-icon.requests {
        background: linear-gradient(135deg, var(--primary-green), var(--light-green));
    }

    .stat-icon.approved {
        background: linear-gradient(135deg, var(--accent-green-approved), #20c997);
    }

    .stat-icon.pending {
        background: linear-gradient(135deg, var(--accent-orange), #ff8c42);
    }

    .stat-icon.inventory {
        background: linear-gradient(135deg, var(--accent-blue), #5dade2);
    }

    .stat-icon.property {
        background: linear-gradient(135deg, var(--accent-purple), #bb8fce);
    }

    .stat-icon.low-stock {
        background: linear-gradient(135deg, var(--accent-red), #ec7063);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 5px;
    }

    .stat-label {
        color: #666;
        font-size: 0.95rem;
        font-weight: 500;
    }

    /* Chart Containers */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }

    .chart-container {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .chart-container h3 {
        margin: 0 0 20px 0;
        color: var(--primary-green);
        font-weight: 600;
        font-size: 1.3rem;
        border-bottom: 3px solid var(--accent-orange);
        padding-bottom: 10px;
    }

    .chart-wrapper {
        position: relative;
        height: 350px;
    }

    .chart-wrapper.small {
        height: 250px;
    }

    /* Table Styles */
    .table-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .table-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 20px;
    }

    .table-header h3 {
        margin: 0;
        font-weight: 600;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: var(--text-dark);
        font-weight: 600;
        border-bottom: 2px solid var(--accent-orange);
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            padding: 15px;
        }

        .content-header {
            padding: 20px;
            flex-direction: column;
            gap: 15px;
        }

        .content-header h1 {
            font-size: 1.6rem;
        }

        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .stat-number {
            font-size: 2rem;
        }

        .chart-wrapper {
            height: 300px;
        }
    }
</style>

<!-- Main Content -->
<div class="main-content">
    <div class="content-header">
        <div>
            <h1>Procurement Statistics & Analytics</h1>
            <p>Comprehensive overview of requests, inventory, and deployments</p>
        </div>
        <a href="procurement.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Procurement
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon requests">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-number"><?= $total_requests ?></div>
            <div class="stat-label">Total Requests</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon approved">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-number"><?= $approved_requests ?></div>
            <div class="stat-label">Approved Requests</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-number"><?= $pending_requests ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon inventory">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-number"><?= $total_inventory_items ?></div>
            <div class="stat-label">Inventory Items (<?= number_format($total_inventory_qty) ?> units)</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon property">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-number"><?= $total_property_items ?></div>
            <div class="stat-label">Property Items (<?= number_format($total_property_qty) ?> units)</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon low-stock">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-number"><?= $low_stock_inventory + $low_stock_property ?></div>
            <div class="stat-label">Low Stock Items</div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Request Type Distribution -->
        <div class="chart-container">
            <h3><i class="fas fa-chart-pie"></i> Request Type Distribution</h3>
            <div class="chart-wrapper small">
                <canvas id="requestTypeChart"></canvas>
            </div>
        </div>

        <!-- Request Workflow Status -->
        <div class="chart-container">
            <h3><i class="fas fa-tasks"></i> Request Workflow Progress</h3>
            <div class="chart-wrapper small">
                <canvas id="workflowChart"></canvas>
            </div>
        </div>

        <!-- Monthly Request Trends -->
        <div class="chart-container" style="grid-column: 1 / -1;">
            <h3><i class="fas fa-chart-line"></i> Monthly Request Trends (Last 12 Months)</h3>
            <div class="chart-wrapper">
                <canvas id="monthlyTrendsChart"></canvas>
            </div>
        </div>

        <!-- Inventory vs Property Comparison -->
        <div class="chart-container">
            <h3><i class="fas fa-balance-scale"></i> Inventory vs Property Items</h3>
            <div class="chart-wrapper small">
                <canvas id="inventoryComparisonChart"></canvas>
            </div>
        </div>

        <!-- Category Distribution -->
        <div class="chart-container">
            <h3><i class="fas fa-layer-group"></i> Top Categories by Stock</h3>
            <div class="chart-wrapper small">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Inventory Status -->
        <div class="chart-container">
            <h3><i class="fas fa-info-circle"></i> Inventory Status Distribution</h3>
            <div class="chart-wrapper small">
                <canvas id="inventoryStatusChart"></canvas>
            </div>
        </div>

        <!-- Property Status -->
        <div class="chart-container">
            <h3><i class="fas fa-info-circle"></i> Property Status Distribution</h3>
            <div class="chart-wrapper small">
                <canvas id="propertyStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Requested Items Table -->
    <div class="table-container">
        <div class="table-header">
            <h3><i class="fas fa-star"></i> Top 10 Most Requested Items</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Name</th>
                        <th>Number of Requests</th>
                        <th>Total Quantity Requested</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($top_items) > 0): ?>
                        <?php foreach ($top_items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                                <td><?= $item['request_count'] ?> requests</td>
                                <td><?= number_format($item['quantity']) ?> units</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Chart.js Global Configuration
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.color = '#666';

    // Color Palette
    const colors = {
        primary: '#073b1d',
        green: '#28a745',
        orange: '#EACA26',
        blue: '#4a90e2',
        red: '#e74c3c',
        purple: '#9b59b6',
        teal: '#1abc9c',
        yellow: '#f39c12',
        pink: '#e91e63'
    };

    // 1. Request Type Distribution Chart
    const requestTypeCtx = document.getElementById('requestTypeChart').getContext('2d');
    new Chart(requestTypeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Consumables', 'Property'],
            datasets: [{
                data: [<?= $consumables_requests ?>, <?= $property_requests ?>],
                backgroundColor: [colors.blue, colors.purple],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // 2. Request Workflow Progress Chart
    const workflowCtx = document.getElementById('workflowChart').getContext('2d');
    new Chart(workflowCtx, {
        type: 'bar',
        data: {
            labels: ['Total', 'Noted', 'Checked', 'Verified', 'Approved', 'Issued'],
            datasets: [{
                label: 'Requests',
                data: [
                    <?= $total_requests ?>,
                    <?= $noted_requests ?>,
                    <?= $checked_requests ?>,
                    <?= $verified_requests ?>,
                    <?= $approved_requests ?>,
                    <?= $issued_requests ?>
                ],
                backgroundColor: [
                    colors.primary,
                    colors.blue,
                    colors.teal,
                    colors.purple,
                    colors.green,
                    colors.orange
                ],
                borderWidth: 0,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // 3. Monthly Request Trends Chart
    const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    new Chart(monthlyTrendsCtx, {
        type: 'line',
        data: {
            labels: [
                <?php foreach ($monthly_data as $data): ?>
                    '<?= date('M Y', strtotime($data['month'] . '-01')) ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Number of Requests',
                data: [
                    <?php foreach ($monthly_data as $data): ?>
                        <?= $data['count'] ?>,
                    <?php endforeach; ?>
                ],
                borderColor: colors.primary,
                backgroundColor: 'rgba(7, 59, 29, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: colors.orange,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // 4. Inventory vs Property Comparison Chart
    const inventoryComparisonCtx = document.getElementById('inventoryComparisonChart').getContext('2d');
    new Chart(inventoryComparisonCtx, {
        type: 'bar',
        data: {
            labels: ['Total Items', 'Total Quantity', 'Low Stock'],
            datasets: [{
                label: 'Inventory',
                data: [<?= $total_inventory_items ?>, <?= $total_inventory_qty ?>, <?= $low_stock_inventory ?>],
                backgroundColor: colors.blue,
                borderRadius: 5
            }, {
                label: 'Property',
                data: [<?= $total_property_items ?>, <?= $total_property_qty ?>, <?= $low_stock_property ?>],
                backgroundColor: colors.purple,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // 5. Category Distribution Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'horizontalBar',
        data: {
            labels: [
                <?php foreach ($category_inventory as $cat): ?>
                    '<?= addslashes($cat['category']) ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Stock Quantity',
                data: [
                    <?php foreach ($category_inventory as $cat): ?>
                        <?= $cat['total_stock'] ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [
                    colors.primary, colors.blue, colors.teal, colors.green, colors.orange,
                    colors.red, colors.purple, colors.yellow, colors.pink, '#34495e'
                ],
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });

    // 6. Inventory Status Distribution Chart
    const inventoryStatusCtx = document.getElementById('inventoryStatusChart').getContext('2d');
    new Chart(inventoryStatusCtx, {
        type: 'pie',
        data: {
            labels: [
                <?php foreach ($inventory_status as $status): ?>
                    '<?= $status['status'] ?? 'Unknown' ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($inventory_status as $status): ?>
                        <?= $status['count'] ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [colors.green, colors.orange, colors.red, colors.blue, colors.purple],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // 7. Property Status Distribution Chart
    const propertyStatusCtx = document.getElementById('propertyStatusChart').getContext('2d');
    new Chart(propertyStatusCtx, {
        type: 'pie',
        data: {
            labels: [
                <?php foreach ($property_status as $status): ?>
                    '<?= $status['status'] ?? 'Unknown' ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($property_status as $status): ?>
                        <?= $status['count'] ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [colors.green, colors.orange, colors.red, colors.blue, colors.purple],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
