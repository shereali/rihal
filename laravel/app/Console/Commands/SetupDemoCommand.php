<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupDemoCommand extends Command
{
    protected $signature = 'setup:demo';
    protected $description = 'Set up demo data for Sabaaq Next';

    public function handle(): int
    {
        $this->info('Setting up demo data...');

        // Call seeders
        $this->call('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->newLine();
        $this->info('Demo setup complete!');
        $this->newLine();
        $this->info('Platform Admin:');
        $this->info('  Email: admin@sabaaq.com');
        $this->info('  Password: Admin123!');
        $this->newLine();
        $this->info('Demo Madrasa (Tenant):');
        $this->info('  Name: দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট');
        $this->info('  Tenant Admin: principal@darulkitabat.org / Principal@#123');
        $this->info('  Teacher:      teacher@darulkitabat.org / Teacher@#123');
        $this->info('  Accountant:   accountant@darulkitabat.org / Account@#123');
        $this->newLine();
        $this->info('Next steps:');
        $this->info('  1. php artisan serve --port 8000');
        $this->info('  2. Visit http://localhost:3000');
        $this->info('  3. Login as admin@sabaaq.com');

        return Command::SUCCESS;
    }
}
