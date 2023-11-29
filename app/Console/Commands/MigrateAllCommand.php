<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute all sub folder for migrate';

    protected $migration_path = './database/migrations/';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Migration started");

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        //echo \Artisan::call('command:');
        \Artisan::call('migrate', ['--path' => "{$this->migration_path}*", '--force' => true]);
        $this->info(\Artisan::output());

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("Migration finished");

        return Command::SUCCESS;
    }
}
