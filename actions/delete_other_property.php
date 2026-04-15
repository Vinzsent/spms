<?php
session_start();
include '../includes/db.php';

// Check if it's an AJAX/JSON request
$contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
$isJson = (stripos($contentType, 'application/json') !== false);
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || $isJson;

// Function to handle response
function sendResponse($success, $message) {
    global $isAjax;
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit();
    } else {
        if ($success) {
            $_SESSION['message'] = $message;
        } else {
            $_SESSION['error'] = $message;
        }
        header("Location: ../pages/other_property_logs.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect the property ID
    if ($isJson) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $property_id = isset($data['id']) ? (int)$data['id'] : 0;
    } else {
        $property_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    }
    
    if ($property_id > 0) {
        try {
            // Check if Property exists first
            $check_sql = "SELECT id, item_name FROM other_property_logs WHERE id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $property_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $property = $result->fetch_assoc();
                $item_name = $property['item_name'];
                
                // Delete the property record
                $delete_sql = "DELETE FROM other_property_logs WHERE id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $property_id);
                
                if ($delete_stmt->execute()) {
                    sendResponse(true, "Property '$item_name' has been deleted successfully.");
                } else {
                    sendResponse(false, "Error deleting property: " . $conn->error);
                }
                $delete_stmt->close();
            } else {
                sendResponse(false, "Property not found.");
            }
            $check_stmt->close();
            
        } catch (Exception $e) {
            sendResponse(false, "An error occurred: " . $e->getMessage());
        }
    } else {
        sendResponse(false, "Invalid property ID parameter.");
    }
} else {
    sendResponse(false, "Invalid request method.");
}
