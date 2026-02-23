<?php

/**
 * ONLINE DATABASE FIX SCRIPT
 * 
 * This script fixes the 'aircon_images' table structure to support multiple images
 * without causing 'Duplicate entry' errors. It also ensures the old 'picture' 
 * column in the 'aircons' table is removed.
 * 
 * INSTRUCTIONS:
 * 1. Upload this file to your 'actions' folder on the server.
 * 2. Access it via your browser: yourdomain.com/darts/actions/fix_online_db.php
 * 3. Delete this file after use for security.
 */

include '../includes/db.php';

echo "<h2>Darts Aircon Management System - Database Fix</h2>";

// 1. Check and Remove 'picture' column from 'aircons'
$check_picture = $conn->query("SHOW COLUMNS FROM aircons LIKE 'picture'");
if ($check_picture && $check_picture->num_rows > 0) {
    echo "Processing 'aircons' table: Removing obsolete 'picture' column... ";
    if ($conn->query("ALTER TABLE aircons DROP COLUMN picture")) {
        echo "<span style='color:green;'>SUCCESS</span><br>";
    } else {
        echo "<span style='color:red;'>ERROR: " . $conn->error . "</span><br>";
    }
} else {
    echo "Table 'aircons': 'picture' column already removed.<br>";
}

// 2. Fix 'aircon_images' table
echo "Processing 'aircon_images' table: Checking structure... ";

// We'll be aggressive here to ensure it's perfect. 
// If it's giving duplicate entry errors, it's safer to recreate (warning: this will clear image paths, 
// but if it's broken and preventing uploads, it's necessary).
// Alternatively, we can try to add the column, but DROP/CREATE is more reliable if the PK is wrong.

$res = $conn->query("SHOW CREATE TABLE aircon_images");
if ($res) {
    $row = $res->fetch_assoc();
    $create_sql = $row['Create Table'];

    // Check if it has the 'id' auto-increment primary key
    if (strpos($create_sql, '`id` int') === false || strpos($create_sql, 'AUTO_INCREMENT') === false) {
        echo "<span style='color:orange;'>INCORRECT STRUCTURE DETECTED</span>. Recreating table... ";
        $conn->query("DROP TABLE IF EXISTS aircon_images");
        $recreate = true;
    } else {
        echo "<span style='color:green;'>ALREADY CORRECT</span>.<br>";
        $recreate = false;
    }
} else {
    echo "Table does not exist. Creating... ";
    $recreate = true;
}

if ($recreate) {
    $create_query = "CREATE TABLE aircon_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        aircon_id INT,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (aircon_id) REFERENCES aircons(aircon_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    if ($conn->query($create_query)) {
        echo "<span style='color:green;'>SUCCESS</span><br>";
    } else {
        echo "<span style='color:red;'>ERROR: " . $conn->error . "</span><br>";
    }
}

echo "<br><b>Action complete. Please delete this script from your server for security.</b>";

$conn->close();
