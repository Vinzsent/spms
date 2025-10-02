<?php
session_start();
include '../includes/db.php';

// ANCHOR: AJAX endpoint for fetching filtered inventory items
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Get parameters
    $search_term = trim($_GET['search'] ?? '');
    $sy_inv_raw = $_GET['sy_inv'] ?? '';
    $page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $records_per_page = 10;
    $offset = ($page - 1) * $records_per_page;
    
    // Parse school year range
    list($sy_inv_start, $sy_inv_end) = parse_school_year_range($sy_inv_raw);
    
    // Build WHERE conditions
    $inv_where_conditions = [];
    
    // Add receiver filter for Supply In-charge
    $inv_where_conditions[] = "i.receiver = 'Supply In-charge'";
    
    // Add search filter if search term is provided
    if (!empty($search_term)) {
        $search_escaped = $conn->real_escape_string($search_term);
        $inv_where_conditions[] = "i.item_name LIKE '%$search_escaped%'";
    }
    
    // Add school year filter if provided
    if ($sy_inv_start && $sy_inv_end) {
        $start_esc = $conn->real_escape_string($sy_inv_start);
        $end_esc = $conn->real_escape_string($sy_inv_end);
        $inv_where_conditions[] = "i.date_created >= '$start_esc' AND i.date_created <= '$end_esc'";
    }
    
    // Build final WHERE clause
    $inv_where = !empty($inv_where_conditions) ? ' WHERE ' . implode(' AND ', $inv_where_conditions) : '';
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM inventory i $inv_where";
    $count_result = $conn->query($count_sql);
    $total_records = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $records_per_page);
    
    // Get inventory data with pagination
    $sql = "SELECT i.*, s.supplier_name 
            FROM inventory i 
            LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
            $inv_where
            ORDER BY i.date_created DESC
            LIMIT $records_per_page OFFSET $offset";
    $result = $conn->query($sql);
    
    // Build HTML for table rows
    $table_rows = '';
    $has_data = false;
    
    if ($result && $result->num_rows > 0) {
        $has_data = true;
        while ($row = $result->fetch_assoc()) {
            $stock_level = 'normal';
            if ($row['current_stock'] == 0) {
                $stock_level = 'out';
            } elseif ($row['current_stock'] <= $row['reorder_level']) {
                $stock_level = 'critical';
            } elseif ($row['current_stock'] <= ($row['reorder_level'] * 1.5)) {
                $stock_level = 'low';
            }
            
            $badge_class = $stock_level == 'out' ? 'danger' : ($stock_level == 'critical' ? 'warning' : 'success');
            
            $table_rows .= '<tr>';
            $table_rows .= '<td>' . htmlspecialchars($row['item_name']) . '</td>';
            $table_rows .= '<td class="text-center"><strong>' . $row['current_stock'] . '</strong></td>';
            $table_rows .= '<td>' . htmlspecialchars($row['unit']) . '</td>';
            $table_rows .= '<td>' . htmlspecialchars($row['brand'] ?? '') . '</td>';
            $table_rows .= '<td>' . htmlspecialchars($row['color'] ?? '') . '</td>';
            $table_rows .= '<td>' . htmlspecialchars($row['size'] ?? '') . '</td>';
            $table_rows .= '<td>' . date('M d, Y', strtotime($row['date_updated'])) . '</td>';
            $table_rows .= '<td><span class="badge bg-' . $badge_class . '">' . ucfirst($stock_level) . '</span></td>';
            $table_rows .= '<td>';
            
            // Stock movement buttons
            $table_rows .= '<div class="half-split btn btn-sm" title="Stock In / Out">';
            $table_rows .= '<span class="half plus" onclick="stockIn(' . (int)$row['inventory_id'] . '); event.stopPropagation();">';
            $table_rows .= '<i class="fas fa-plus"></i>';
            $table_rows .= '</span>';
            $table_rows .= '<span class="half minus" onclick="stockOut(' . (int)$row['inventory_id'] . '); event.stopPropagation();">';
            $table_rows .= '<i class="fas fa-minus"></i>';
            $table_rows .= '</span>';
            $table_rows .= '</div>';
            
            // Edit button
            $table_rows .= '<button class="btn btn-sm btn-info" title="Edit" onclick="openEditInventoryModal(';
            $table_rows .= (int)$row['inventory_id'] . ', ';
            $table_rows .= json_encode($row['item_name']) . ', ';
            $table_rows .= json_encode($row['category']) . ', ';
            $table_rows .= json_encode($row['brand'] ?? '') . ', ';
            $table_rows .= json_encode($row['color'] ?? '') . ', ';
            $table_rows .= json_encode($row['size'] ?? '') . ', ';
            $table_rows .= json_encode($row['type'] ?? '') . ', ';
            $table_rows .= json_encode($row['description'] ?? '') . ', ';
            $table_rows .= json_encode($row['unit']) . ', ';
            $table_rows .= (int)$row['current_stock'] . ', ';
            $table_rows .= (int)$row['reorder_level'] . ', ';
            $table_rows .= (int)$row['supplier_id'] . ', ';
            $table_rows .= json_encode($row['location'] ?? '') . ', ';
            $table_rows .= (float)$row['unit_cost'];
            $table_rows .= ')"><i class="fas fa-edit"></i></button>';
            
            $table_rows .= '</td>';
            $table_rows .= '</tr>';
        }
    }
    
    // Build pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        $pagination_html .= '<nav>';
        $pagination_html .= '<ul class="pagination justify-content-center mt-3" id="paginationContainer">';
        
        // Previous button
        if ($page > 1) {
            $prev_page = $page - 1;
            $pagination_html .= '<li class="page-item">';
            $pagination_html .= '<a class="page-link" href="#" onclick="loadInventoryItems(' . $prev_page . ', \'' . htmlspecialchars($search_term) . '\', \'' . htmlspecialchars($sy_inv_raw) . '\'); return false;">&laquo;</a>';
            $pagination_html .= '</li>';
        }
        
        // Page numbers - show max 7 page numbers
        $start_page = max(1, $page - 3);
        $end_page = min($total_pages, $page + 3);
        
        if ($start_page > 1) {
            $pagination_html .= '<li class="page-item">';
            $pagination_html .= '<a class="page-link" href="#" onclick="loadInventoryItems(1, \'' . htmlspecialchars($search_term) . '\', \'' . htmlspecialchars($sy_inv_raw) . '\'); return false;">1</a>';
            $pagination_html .= '</li>';
            if ($start_page > 2) {
                $pagination_html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = $i == $page ? ' active' : '';
            $pagination_html .= '<li class="page-item' . $active_class . '">';
            $pagination_html .= '<a class="page-link" href="#" onclick="loadInventoryItems(' . $i . ', \'' . htmlspecialchars($search_term) . '\', \'' . htmlspecialchars($sy_inv_raw) . '\'); return false;">' . $i . '</a>';
            $pagination_html .= '</li>';
        }
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                $pagination_html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $pagination_html .= '<li class="page-item">';
            $pagination_html .= '<a class="page-link" href="#" onclick="loadInventoryItems(' . $total_pages . ', \'' . htmlspecialchars($search_term) . '\', \'' . htmlspecialchars($sy_inv_raw) . '\'); return false;">' . $total_pages . '</a>';
            $pagination_html .= '</li>';
        }
        
        // Next button
        if ($page < $total_pages) {
            $next_page = $page + 1;
            $pagination_html .= '<li class="page-item">';
            $pagination_html .= '<a class="page-link" href="#" onclick="loadInventoryItems(' . $next_page . ', \'' . htmlspecialchars($search_term) . '\', \'' . htmlspecialchars($sy_inv_raw) . '\'); return false;">&raquo;</a>';
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
        'total_records' => $total_records,
        'current_page' => $page,
        'total_pages' => $total_pages
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching inventory items: ' . $e->getMessage()
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

