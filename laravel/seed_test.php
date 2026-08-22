<?php
require __DIR__.'/bootstrap/app.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'db:seed', '--class' => 'DemoTenantSeeder']),
    new Symfony\Component\Console\Output\NullOutput()
);
echo "seed exit: $status\n";

