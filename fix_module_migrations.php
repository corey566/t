<?php

exec('find Modules -name "*.php" -path "*/Migrations/*" -exec grep -l "MODIFY" {} \;', $files);

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
        
        // Make sure DB is imported
        if (strpos($content, 'use Illuminate\Support\Facades\DB;') === false) {
            $content = str_replace("use Illuminate\Database\Migrations\Migration;", "use Illuminate\Database\Migrations\Migration;\nuse Illuminate\Support\Facades\DB;", $content);
        }
        
        file_put_contents($file, $content);
        echo "Fixed: " . $file . "\n";
    }
}

echo "Done!\n";
