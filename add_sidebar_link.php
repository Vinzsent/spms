<?php
$pages_dir = 'c:\\xampp\\htdocs\\darts\\pages\\';
$files = glob($pages_dir . '*.php');

$link = '<?php if (in_array($user_type, [\'supplyincharge\', \'admin\'])): ?>
                <li><a href="supply_offices_request.php" class="nav-link"><i class="fas fa-building"></i> Office Requisitions</a></li>
            <?php endif; ?>';

foreach ($files as $file) {
    if (basename($file) == 'supply_offices_request.php' || basename($file) == 'office_supply_requests.php') continue;
    
    $content = file_get_contents($file);
    if (strpos($content, 'class="sidebar"') !== false) {
        if (strpos($content, 'supply_offices_request.php') === false) {
            $search1 = '<li><a href="../logout.php"';
            $search2 = '<li><a href="logout.php"';
            
            $pos = strpos($content, $search1);
            if ($pos === false) $pos = strpos($content, $search2);
            
            if ($pos !== false) {
                // Determine indent level based on the line
                $last_lf = strrpos(substr($content, 0, $pos), "\n");
                $indent = ($last_lf !== false) ? substr($content, $last_lf + 1, $pos - $last_lf - 1) : "                ";
                
                $new_content_part = $link . "\n" . $indent;
                $content = substr_replace($content, $new_content_part, $pos, 0);
                file_put_contents($file, $content);
                echo "Added to " . basename($file) . "\n";
            }
        } else {
            echo "Already exists in " . basename($file) . "\n";
        }
    }
}
echo "Done.\n";
