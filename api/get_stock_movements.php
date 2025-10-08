<?php
session_start();
include '../includes/db.php';

// ANCHOR: AJAX endpoint for fetching filtered stock movements
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Get parameters
    $sy_logs_raw = $_GET['sy_logs'] ?? '';
    $logs_page = isset($_GET['logs_page']) && $_GET['logs_page'] > 0 ? (int)$_GET['logs_page'] : 1;
    $logs_per_page = 10;
    $logs_offset = ($logs_page - 1) * $logs_per_page;
    
    // Parse school year range
    list($sy_logs_start, $sy_logs_end) = parse_school_year_range($sy_logs_raw);
    
    // Build WHERE conditions
    $logs_where_conditions = [];
    
    // Add receiver filter for Supply In-charge
    $logs_where_conditions[] = "sl.receiver = 'Supply In-charge'";
    
    // Add school year filter if provided
    if ($sy_logs_start && $sy_logs_end) {
        $start_esc = $conn->real_escape_string($sy_logs_start);
        $end_esc = $conn->real_escape_string($sy_logs_end);
        $logs_where_conditions[] = "sl.date_created >= '$start_esc' AND sl.date_created <= '$end_esc'";
    }
    
    // Build final WHERE clause
    $logs_where = !empty($logs_where_conditions) ? ' WHERE ' . implode(' AND ', $logs_where_conditions) : '';
    
    // Get total count for pagination
    $logs_count_sql = "SELECT COUNT(*) as total 
                       FROM stock_logs sl 
                       LEFT JOIN inventory i ON sl.inventory_id = i.inventory_id 
                       LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
                       $logs_where";
    $logs_count_result = $conn->query($logs_count_sql);
    $total_logs = $logs_count_result->fetch_assoc()['total'];
    $total_logs_pages = ceil($total_logs / $logs_per_page);
    
    // Get stock movements data
    $stock_logs_sql = "SELECT sl.*, i.item_name, s.supplier_name 
                       FROM stock_logs sl 
                       LEFT JOIN inventory i ON sl.inventory_id = i.inventory_id 
                       LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
                       $logs_where
                       ORDER BY sl.date_created DESC 
                       LIMIT $logs_per_page OFFSET $logs_offset";
    $stock_logs_result = $conn->query($stock_logs_sql);
    
    // Build HTML for table rows
    $table_rows = '';
    $has_data = false;
    
    if ($stock_logs_result && $stock_logs_result->num_rows > 0) {
        $has_data = true;
        while ($log = $stock_logs_result->fetch_assoc()) {
            $badge_class = $log['movement_type'] == 'IN' ? 'success' : 'warning';
            $table_rows .= '<tr>';
            $table_rows .= '<td>' . date('M d, Y H:i', strtotime($log['date_created'])) . '</td>';
            $table_rows .= '<td>' . htmlspecialchars($log['item_name']) . '</td>';
            $table_rows .= '<td><span class="badge bg-' . $badge_class . '">' . $log['movement_type'] . '</span></td>';
            $table_rows .= '<td>' . $log['quantity'] . '</td>';
            $table_rows .= '<td>' . $log['previous_stock'] . '</td>';
            $table_rows .= '<td>' . $log['new_stock'] . '</td>';
            $table_rows .= '<td>' . htmlspecialchars($log['receiver'] ?? 'N/A') . '</td>';
            $table_rows .= '<td>' . htmlspecialchars($log['notes']) . '</td>';
            $table_rows .= '<td>';
            $table_rows .= '<button class="btn btn-sm btn-info" onclick="editMovement(' . $log['log_id'] . ')">';
            $table_rows .= '<i class="fas fa-edit"></i>';
            $table_rows .= '</button>';
            $table_rows .= '</td>';
            $table_rows .= '</tr>';
        }
    }
    
    // Build pagination HTML
    $pagination_html = '';
    if ($total_logs_pages > 1) {
        $pagination_html .= '<nav aria-label="Stock movements pagination">';
        $pagination_html .= '<ul class="pagination justify-content-center">';
        
        // Previous button
        if ($logs_page > 1) {
            $prev_page = $logs_page - 1;
            $pagination_html .= '<li class="page-item">';
            $pagination_html .= '<a class="page-link" href="#" onclick="loadStockMovements(' . $prev_page . ', \'' . htmlspecialchars($sy_logs_raw) . '\'); return false;">Previous</a>';
            $pagination_html .= '</li>';
        }
        
        // Page numbers
        for ($i = max(1, $logs_page - 2); $i <= min($total_logs_pages, $logs_page + 2); $i++) {
            $active_class = $i == $logs_page ? ' active' : '';
            $pagination_html .= '<li class="page-item' . $active_class . '">';
            $pagination_html .= '<a class="page-link" href="#" onclick="loadStockMovements(' . $i . ', \'' . htmlspecialchars($sy_logs_raw) . '\'); return false;">' . $i . '</a>';
            $pagination_html .= '</li>';
        }
        
        // Next button
        if ($logs_page < $total_logs_pages) {
            $next_page = $logs_page + 1;
            $pagination_html .= '<li class="page-item">';
            $pagination_html .= '<a class="page-link" href="#" onclick="loadStockMovements(' . $next_page . ', \'' . htmlspecialchars($sy_logs_raw) . '\'); return false;">Next</a>';
            $pagination_html .= '</li>';
        }
        
        $pagination_html .= '</ul>';
        $pagination_html .= '</nav>';
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'table_rows' => $table_rows,
        'pagination' => $pagination_html,
        'has_data' => $has_data,
        'total_logs' => $total_logs,
        'current_page' => $logs_page,
        'total_pages' => $total_logs_pages
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching stock movements: ' . $e->getMessage()
    ]);
}

/**
 * Parse school year string into start and end dates
 * Format: "2023-2024" -> ["2023-07-01", "2024-06-30"]
 */
function parse_school_year_range($sy_string) {
    if (empty($sy_string)) {
        return [null, null];
    }
    
    if (preg_match('/^(\d{4})-(\d{4})$/', $sy_string, $matches)) {
        $start_year = (int)$matches[1];
        $end_year = (int)$matches[2];
        
        $start_date = $start_year . '-07-01';
        $end_date = $end_year . '-06-30';
        
        return [$start_date, $end_date];
    }
    
    return [null, null];
}
?>
