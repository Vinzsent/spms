<?php
session_start();
header('Content-Type: application/json');

// Get raw input
$raw_input = file_get_contents('php://input');

// Log everything for debugging
$debug_info = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'raw_input_length' => strlen($raw_input),
    'raw_input_first_100' => substr($raw_input, 0, 100),
    'raw_input_last_100' => substr($raw_input, -100),
    'json_decode_result' => null,
    'json_error' => null
];

// Try to decode JSON
$decoded = json_decode($raw_input, true);
$debug_info['json_decode_result'] = $decoded;
$debug_info['json_error'] = json_last_error_msg();

echo json_encode([
    'success' => true,
    'debug' => $debug_info,
    'message' => 'Debug information collected'
]);
?>
