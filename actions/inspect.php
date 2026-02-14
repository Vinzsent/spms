<?php
echo "Current Directory: " . __DIR__ . "\n";
echo "Parent Directory: " . dirname(__DIR__) . "\n";
echo "Checking ../includes/db.php: " . (file_exists('../includes/db.php') ? 'Found' : 'Not Found') . "\n";
echo "Checking ../../includes/db.php: " . (file_exists('../../includes/db.php') ? 'Found' : 'Not Found') . "\n";
echo "Checking c:/Users/vince/AppData/Local/Temp/includes/db.php: " . (file_exists('c:/Users/vince/AppData/Local/Temp/includes/db.php') ? 'Found' : 'Not Found') . "\n";
