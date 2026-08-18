<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): BaseTestCase
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
