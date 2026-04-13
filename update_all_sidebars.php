<?php
$pages_dir = 'c:\\xampp\\htdocs\\darts\\pages\\';
$files = glob($pages_dir . '*.php');

$other_property_link = '<li><a href="other_property_logs.php" class="nav-link">
                        <i class="fas fa-box"></i> Other Property Logs
                    </a></li>';

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'class="sidebar"') !== false) {
        // Check if "Other Property Logs" link already exists
        if (strpos($content, 'other_property_logs.php') === false) {
            echo "Adding Other Property Logs to $file\n";
            // Find a good place to insert - after "Borrower Forms" or before "Logout"
            if (strpos($content, 'borrowers_forms.php') !== false) {
                // Insert after borrower forms
                $search = '<li><a href="borrowers_forms.php" class="nav-link';
                // Find the closing </li> of that entry
                $pos = strpos($content, '</li>', strpos($content, $search));
                if ($pos !== false) {
                    $new_content = substr($content, 0, $pos + 5) . "\n                " . $other_property_link . substr($content, $pos + 5);
                    file_put_contents($file, $new_content);
                }
            } else if (strpos($content, 'logout.php') !== false) {
                // Insert before logout
                $search = '<li><a href="../logout.php"';
                if (strpos($content, $search) === false) $search = '<li><a href="logout.php"';
                
                $pos = strpos($content, $search);
                if ($pos !== false) {
                    $new_content = substr($content, 0, $pos) . $other_property_link . "\n                " . substr($content, $pos);
                    file_put_contents($file, $new_content);
                }
            }
        } else {
             echo "Other Property Logs already in $file\n";
        }
    }
}
echo "Global sidebar update complete.\n";
