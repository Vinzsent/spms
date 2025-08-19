<?php
/**
 * API endpoint to generate hierarchical category dropdown options
 * This generates the same structure as shown in the add_supply_request.php modal
 */

// Set content type to JSON for API response
header('Content-Type: application/json');

// Include database connection
include __DIR__ . '/../includes/db.php';

// Function to generate hierarchical dropdown options
function generateHierarchicalOptions($conn, $format = 'html') {
    $options = '';
    $data_array = [];
    
    // Fetch all data organized by account type -> category -> subcategory
    $query = "
        SELECT 
            at.id as account_type_id,
            at.name as account_type_name,
            c.id as category_id,
            c.name as category_name,
            s.id as subcategory_id,
            s.name as subcategory_name
        FROM account_types at
        LEFT JOIN categories c ON at.id = c.account_type_id
        LEFT JOIN subcategories s ON c.id = s.category_id
        WHERE at.name IN ('Assets', 'Expenses')
        ORDER BY 
            CASE 
                WHEN at.name = 'Assets' THEN 1 
                WHEN at.name = 'Expenses' THEN 2 
                ELSE 3 
            END,
            c.name, s.name
    ";
    
    $result = $conn->query($query);
    $structured_data = [];
    
    // Organize data into hierarchical structure
    while ($row = $result->fetch_assoc()) {
        $account_type = $row['account_type_name'];
        $category = $row['category_name'];
        $subcategory = $row['subcategory_name'];
        
        if (!isset($structured_data[$account_type])) {
            $structured_data[$account_type] = [];
        }
        
        if ($category && !isset($structured_data[$account_type][$category])) {
            $structured_data[$account_type][$category] = [];
        }
        
        if ($subcategory) {
            $structured_data[$account_type][$category][] = [
                'id' => $row['subcategory_id'],
                'name' => $subcategory,
                'value' => $subcategory  // For compatibility with existing forms
            ];
        }
    }
    
    if ($format === 'json') {
        return $structured_data;
    }
    
    // Generate HTML options (similar to add_supply_request.php)
    foreach ($structured_data as $account_type => $categories) {
        if (empty($categories)) continue;
        
        // Add account type header
        $options .= '<optgroup label="' . strtoupper($account_type) . '" class="text-center"></optgroup>';
        
        // Special handling for additional categories that might not be in the database yet
        if ($account_type === 'Assets') {
            // Add School Building entries if they don't exist in database
            $options .= '<option>School Building – Main Campus</option>';
            $options .= '<option>School Building – BED Campus</option>';
        }
        
        foreach ($categories as $category_name => $subcategories) {
            if (empty($subcategories)) continue;
            
            // Add category group
            $options .= '<optgroup label="' . htmlspecialchars($category_name) . '">';
            
            // Special case for expanding subcategory names to match the required format
            foreach ($subcategories as $subcategory) {
                $subcategory_name = $subcategory['name'];
                
                // Expand subcategory names to include campus information
                if (in_array($subcategory_name, ['Furniture and Fixtures', 'Office Equipment', 'Computers'])) {
                    $options .= '<option value="' . $subcategory['id'] . '">' . htmlspecialchars($subcategory_name) . ' – Main Campus</option>';
                    $options .= '<option value="' . $subcategory['id'] . '">' . htmlspecialchars($subcategory_name) . ' – BED Campus</option>';
                } elseif ($subcategory_name === 'Laboratory Equipment') {
                    // Laboratory Equipment has specific department variations
                    $lab_departments = ['CJE', 'HME', 'Science (TED)', 'Science (BED)', 'Physics (BED)', 'TLE'];
                    foreach ($lab_departments as $dept) {
                        $options .= '<option value="' . $subcategory['id'] . '">Laboratory Equipment – ' . $dept . '</option>';
                    }
                } elseif (in_array($subcategory_name, ['Supplies and Materials'])) {
                    // Expand Supplies and Materials
                    $supply_types = [
                        'Office Supplies (Main/BED Campuses)',
                        'Medical Supplies (Main/BED Campuses)',
                        'School Supplies (Main/BED Campuses)',
                        'Textbooks (Main/BED Campuses)'
                    ];
                    foreach ($supply_types as $supply_type) {
                        $options .= '<option value="' . $subcategory['id'] . '">' . $supply_type . '</option>';
                    }
                } elseif ($subcategory_name === 'Laboratory Expenses') {
                    // Handle Laboratory Expenses
                    $options .= '<option value="' . $subcategory['id'] . '">Library Expenses (Main/BED Campuses)</option>';
                    $options .= '<option value="' . $subcategory['id'] . '">Medical Expenses (Main/BED Campuses)</option>';
                    $options .= '<option value="' . $subcategory['id'] . '">Repairs and Maintenance (Main/BED Campuses)</option>';
                    $options .= '<option value="' . $subcategory['id'] . '">Janitorial & Cleaning Expenses (Main/BED Campuses)</option>';
                    $options .= '<option value="' . $subcategory['id'] . '">Testing Materials (Main/BED Campuses)</option>';
                } else {
                    // Default case
                    $options .= '<option value="' . $subcategory['id'] . '">' . htmlspecialchars($subcategory_name) . '</option>';
                }
            }
            
            $options .= '</optgroup>';
        }
        
        // Add additional categories for Assets
        if ($account_type === 'Assets') {
            $options .= '<optgroup label="Others">';
            $options .= '<option>Vehicle</option>';
            $options .= '</optgroup>';
            
            $options .= '<optgroup label="Intangible Assets">';
            $options .= '<option>Software</option>';
            $options .= '<option>Patents and Licenses</option>';
            $options .= '</optgroup>';
        }
    }
    
    return $options;
}

try {
    $format = $_GET['format'] ?? 'html';
    
    if ($format === 'json') {
        echo json_encode([
            'success' => true,
            'data' => generateHierarchicalOptions($conn, 'json')
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'html' => generateHierarchicalOptions($conn, 'html')
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
