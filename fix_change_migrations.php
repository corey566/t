<?php

exec('grep -l "CHANGE " database/migrations/*.php', $files);

foreach ($files as $file) {
    $file = trim($file);
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    if (strpos($content, 'getDriverName') !== false) {
        echo "Skipping (already fixed): " . basename($file) . "\n";
        continue;
    }
    
    // Extract the up() method content
    if (preg_match('/public function up\(\)\s*\{(.*?)\n    \}/s', $content, $match)) {
        $upContent = $match[1];
        
        // Wrap everything in MySQL check
        $newUpContent = "\n        if (DB::getDriverName() === 'mysql') {" . $upContent . "\n        }\n    ";
        
        $content = str_replace("public function up()\n    {" . $upContent . "\n    }", "public function up()\n    {" . $newUpContent . "}", $content);
        
        file_put_contents($file, $content);
        echo "Fixed: " . basename($file) . "\n";
    }
}

echo "Done!\n";
