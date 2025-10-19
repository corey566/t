<?php

// Restore original files first
exec('git checkout database/migrations/*.php 2>&1', $output, $return_code);

$migrations = glob('database/migrations/*.php');

foreach ($migrations as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Skip if already has getDriverName check
    if (strpos($content, 'getDriverName') !== false && strpos($content, '2018_01_08_123327') === false) {
        continue;
    }
    
    // Skip if doesn't have MODIFY
    if (strpos($content, 'MODIFY') === false) {
        continue;
    }
    
    echo "Fixing: $file\n";
    
    // Find the up() method
    if (preg_match('/public function up\(\)\s*\{(.*?)\n    \}/s', $content, $match)) {
        $upMethod = $match[1];
        $originalUpMethod = $upMethod;
        
        // Wrap all DB::statement calls that contain MODIFY with MySQL check
        $upMethod = preg_replace_callback('/(\s+)(DB::statement\([^;]+MODIFY[^;]+\);)/s', function($m) {
            return $m[1] . "if (DB::getDriverName() === 'mysql') {\n        " . $m[1] . trim($m[2]) . "\n" . $m[1] . "}";
        }, $upMethod);
        
        // Replace the up method in content
        if ($upMethod !== $originalUpMethod) {
            $content = str_replace($originalUpMethod, $upMethod, $content);
            file_put_contents($file, $content);
        }
    }
}

echo "Done!\n";
