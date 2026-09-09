<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AuditLog;

$l = AuditLog::first();
if (!$l) {
    echo "No audit log row to test.\n";
    exit(0);
}

try {
    $l->delete();
    echo "DELETE ALLOWED (BAD)\n";
} catch (\Throwable $e) {
    echo "DELETE BLOCKED: " . substr($e->getMessage(), 0, 70) . "\n";
}

try {
    $l->update(['type' => 'hacked']);
    echo "UPDATE ALLOWED (BAD)\n";
} catch (\Throwable $e) {
    echo "UPDATE BLOCKED: " . substr($e->getMessage(), 0, 70) . "\n";
}
