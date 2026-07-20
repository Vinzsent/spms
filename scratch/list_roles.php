<?php
include 'c:/xampp/htdocs/darts/includes/db.php';
$res = $conn->query('SELECT DISTINCT user_type FROM user');
while($row = $res->fetch_assoc()) {
    echo $row['user_type'] . "\n";
}
?>
