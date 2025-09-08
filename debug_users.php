<?php
include 'includes/db.php';

echo "<h2>Debug: User Table Contents</h2>";

// Check if username column exists
$result = $conn->query("DESCRIBE user");
echo "<h3>Table Structure:</h3>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    foreach ($row as $value) {
        echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

// Show all usernames
$result = $conn->query("SELECT id, username, email, user_type FROM user ORDER BY id");
echo "<h3>All Users:</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>User Type</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['username'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['email'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['user_type'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Search for specific username
$search_username = '18-000124';
$stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
$stmt->bind_param("s", $search_username);
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>Search for username '$search_username':</h3>";
if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " user(s)";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>" . print_r($row, true) . "</pre>";
    }
} else {
    echo "No user found with username '$search_username'";
}

// Search with LIKE to find similar usernames
$stmt = $conn->prepare("SELECT username FROM user WHERE username LIKE ?");
$like_pattern = '%' . $search_username . '%';
$stmt->bind_param("s", $like_pattern);
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>Similar usernames (LIKE search):</h3>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "- " . htmlspecialchars($row['username']) . "<br>";
    }
} else {
    echo "No similar usernames found";
}
?>
