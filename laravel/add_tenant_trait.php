<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

$modelsPath = app_path('Models');
$files = glob($modelsPath . '/*.php');

foreach ($files as $file) {
    $className = 'App\\Models\\' . basename($file, '.php');
    
    if (class_exists($className)) {
        try {
            $model = new $className;
            $table = $model->getTable();
            
            if (Schema::hasColumn($table, 'tenant_id')) {
                $content = file_get_contents($file);
                
                // If it doesn't already use BelongsToTenant
                if (!str_contains($content, 'use App\\Traits\\BelongsToTenant;')) {
                    // Add the import
                    $content = preg_replace('/namespace App\\\\Models;/', "namespace App\Models;\n\nuse App\Traits\BelongsToTenant;", $content);
                    
                    // Add the trait inside the class
                    // Find the first { after class definition
                    $content = preg_replace('/class\s+[^{]+\{/', "$0\n    use BelongsToTenant;\n", $content);
                    
                    file_put_contents($file, $content);
                    echo "Added BelongsToTenant to $className\n";
                }
            }
        } catch (\Exception $e) {
            echo "Skipping $className: " . $e->getMessage() . "\n";
        }
    }
}
