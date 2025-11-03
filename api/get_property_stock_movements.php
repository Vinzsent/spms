<?php
session_start();
include '../includes/db.php';

// AJAX endpoint for fetching property stock movements with pagination
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
    $logs_per_page = 5;
    $logs_offset = ($logs_page - 1) * $logs_per_page;
    
    // Parse school year range
    list($sy_logs_start, $sy_logs_end) = parse_school_year_range($sy_logs_raw);
    
    // Build WHERE conditions
    $logs_where_conditions = [];
    
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
                       FROM property_stock_logs sl 
                       LEFT JOIN property_inventory pi ON sl.inventory_id = pi.inventory_id 
                       LEFT JOIN supplier s ON pi.supplier_id = s.supplier_id 
                       $logs_where";
    $logs_count_result = $conn->query($logs_count_sql);
    $total_logs = $logs_count_result->fetch_assoc()['total'];
    $logs_total_pages = ceil($total_logs / $logs_per_page);
    
    // Get stock movements data
    $stock_logs_sql = "SELECT sl.*, pi.item_name, s.supplier_name 
                       FROM property_stock_logs sl 
                       LEFT JOIN property_inventory pi ON sl.inventory_id = pi.inventory_id 
                       LEFT JOIN supplier s ON pi.supplier_id = s.supplier_id 
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
            $table_rows .= '<button class="btn btn-sm btn-info" title="Edit" onclick=\'openEditStockMovementModal(';
            $table_rows .= (int)$log['log_id'] . ', ';
            $table_rows .= (int)$log['inventory_id'] . ', ';
            $table_rows .= json_encode($log['item_name']) . ', ';
            $table_rows .= json_encode($log['movement_type']) . ', ';
            $table_rows .= (int)$log['quantity'] . ', ';
            $table_rows .= (int)$log['previous_stock'] . ', ';
            $table_rows .= (int)$log['new_stock'] . ', ';
            $table_rows .= json_encode($log['receiver'] ?? '') . ', ';
            $table_rows .= json_encode($log['notes'] ?? '');
            $table_rows .= ')\'>';
            $table_rows .= '<i class="fas fa-edit"></i>';
            $table_rows .= '</button>';
            $table_rows .= '</td>';
            $table_rows .= '</tr>';
        }
    } else {
        $table_rows .= '<tr>';
        $table_rows .= '<td colspan="9" class="text-center py-4">';
        $table_rows .= '<i class="fas fa-history fa-3x text-muted mb-3"></i>';
        $table_rows .= '<p class="text-muted">No stock movements recorded</p>';
        $table_rows .= '</td>';
        $table_rows .= '</tr>';
    }
    
    // Build pagination HTML
    $pagination_html = '';
    if ($logs_total_pages > 1) {
        $pagination_html .= '<nav>';
        $pagination_html .= '<ul class="pagination justify-content-center mt-3">';
        
        // Previous button
        $prev_disabled = $logs_page <= 1 ? ' disabled' : '';
        $pagination_html .= '<li class="page-item' . $prev_disabled . '">';
        $pagination_html .= '<a class="page-link" href="#" onclick="loadStockMovements(' . ($logs_page - 1) . '); return false;">&laquo;</a>';
        $pagination_html .= '</li>';
        
        // Page numbers
        for ($i = 1; $i <= $logs_total_pages; $i++) {
            $active_class = ((int)$i === (int)$logs_page) ? ' active' : '';
            $pagination_html .= '<li class="page-item' . $active_class . '">';
            $pagination_html .= '<a class="page-link" href="#" onclick="loadStockMovements(' . $i . '); return false;">' . $i . '</a>';
            $pagination_html .= '</li>';
        }
        
        // Next button
        $next_disabled = $logs_page >= $logs_total_pages ? ' disabled' : '';
        $pagination_html .= '<li class="page-item' . $next_disabled . '">';
        $pagination_html .= '<a class="page-link" href="#" onclick="loadStockMovements(' . ($logs_page + 1) . '); return false;">&raquo;</a>';
        $pagination_html .= '</li>';
        
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
        'total_pages' => $logs_total_pages
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
