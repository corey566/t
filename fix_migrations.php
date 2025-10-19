<?php

$migrations = glob('database/migrations/*.php');

foreach ($migrations as $file) {
    $content = file_get_contents($file);
    
    // Skip if already has getDriverName check
    if (strpos($content, 'getDriverName') !== false) {
        continue;
    }
    
    // Skip if doesn't have MODIFY COLUMN
    if (strpos($content, 'MODIFY COLUMN') === false && strpos($content, 'MODIFY ') === false) {
        continue;
    }
    
    echo "Fixing: $file\n";
    
    // Find all DB::statement lines with MODIFY
    preg_match_all('/DB::statement\([\'"](.+?)[\'"](?:,.*?)?\);/s', $content, $matches);
    
    foreach ($matches[0] as $index => $fullMatch) {
        $statement = $matches[1][$index];
        
        // Only process if it contains MODIFY
        if (strpos($statement, 'MODIFY') !== false) {
            // Wrap with MySQL-only execution
            $replacement = "if (DB::getDriverName() === 'mysql') {\n            DB::statement(\"$statement\");\n        }";
            $content = str_replace($fullMatch, $replacement, $content);
        }
    }
    
    file_put_contents($file, $content);
}

echo "Done!\n";
