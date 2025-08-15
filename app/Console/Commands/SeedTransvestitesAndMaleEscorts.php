<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\TransvestiteMaleEscortSeeder;

class SeedTransvestitesAndMaleEscorts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:transvestites-male-escorts {--force : Force the operation to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with sample transvestites and male escorts data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will create sample transvestites and male escorts. Do you wish to continue?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('🌺 Starting to seed transvestites and male escorts...');

        try {
            $seeder = new TransvestiteMaleEscortSeeder();
            $seeder->run();

            $this->info('✅ Successfully seeded transvestites and male escorts!');
            $this->newLine();
            $this->info('📋 Sample accounts created:');
            $this->info('   • 3 travestis (transvestite1@teste.com, transvestite2@teste.com, transvestite3@teste.com)');
            $this->info('   • 3 male escorts (garoto1@teste.com, garoto2@teste.com, garoto3@teste.com)');
            $this->info('   • All passwords: password');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error seeding transvestites and male escorts: ' . $e->getMessage());
            return 1;
        }
    }
}
