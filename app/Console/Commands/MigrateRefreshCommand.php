<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateRefreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate-refresh';

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
        $this->info("Migration REFRESH started");

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        //echo \Artisan::call('command:');
        \Artisan::call('migrate:refresh', ['--path' => "{$this->migration_path}*", '--force' => true]);
        $this->info(\Artisan::output());

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("Migration REFRESH finished");

        return Command::SUCCESS;
    }
}
