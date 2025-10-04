<?php
// ANCHOR: Debug script to help identify API path issues in production
echo "<h2>API Path Debug Information</h2>";

echo "<h3>Server Variables:</h3>";
echo "<ul>";
echo "<li><strong>REQUEST_SCHEME:</strong> " . ($_SERVER['REQUEST_SCHEME'] ?? 'Not set') . "</li>";
echo "<li><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</li>";
echo "<li><strong>PHP_SELF:</strong> " . ($_SERVER['PHP_SELF'] ?? 'Not set') . "</li>";
echo "<li><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "</li>";
echo "<li><strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "</li>";
echo "<li><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Not set') . "</li>";
echo "</ul>";

echo "<h3>Calculated Base URLs:</h3>";
$base_url_1 = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$base_url_2 = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

echo "<ul>";
echo "<li><strong>Base URL (Method 1):</strong> " . $base_url_1 . "</li>";
echo "<li><strong>Base URL (Method 2):</strong> " . $base_url_2 . "</li>";
echo "</ul>";

echo "<h3>API Endpoint URLs:</h3>";
echo "<ul>";
echo "<li><strong>Stock Movements API:</strong> <a href='" . $base_url_1 . "/api/get_stock_movements.php' target='_blank'>" . $base_url_1 . "/api/get_stock_movements.php</a></li>";
echo "</ul>";

echo "<h3>File Structure Check:</h3>";
$api_file = dirname($_SERVER['PHP_SELF']) . '/api/get_stock_movements.php';
$full_path = $_SERVER['DOCUMENT_ROOT'] . $api_file;

echo "<ul>";
echo "<li><strong>API File Path:</strong> " . $api_file . "</li>";
echo "<li><strong>Full Server Path:</strong> " . $full_path . "</li>";
echo "<li><strong>File Exists:</strong> " . (file_exists($full_path) ? 'YES' : 'NO') . "</li>";
echo "</ul>";

echo "<h3>Test API Call:</h3>";
$test_url = $base_url_1 . '/api/get_stock_movements.php?logs_page=1';
echo "<p>Test URL: <a href='" . $test_url . "' target='_blank'>" . $test_url . "</a></p>";

// Test if the API file is accessible
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-type: application/json\r\n'
    ]
]);

echo "<h3>API Response Test:</h3>";
$response = @file_get_contents($test_url, false, $context);
if ($response !== false) {
    echo "<p><strong>API Response:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
} else {
    echo "<p><strong>Error:</strong> Could not access API endpoint</p>";
    $error = error_get_last();
    if ($error) {
        echo "<p>Error details: " . htmlspecialchars($error['message']) . "</p>";
    }
}

echo "<h3>JavaScript Test:</h3>";
echo "<script>";
echo "console.log('Current location:', window.location.href);";
echo "console.log('Current pathname:', window.location.pathname);";
echo "console.log('Current origin:', window.location.origin);";
echo "console.log('Server base URL:', '" . $base_url_1 . "');";
echo "</script>";
?>
