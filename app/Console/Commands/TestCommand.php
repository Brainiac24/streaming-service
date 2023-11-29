<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute all sub folder for migrate';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        /*Carbon::setLocale('ru');

        $date = Carbon::now()->isoFormat('D MMMM Y в HH:mm (МСК)');

        $this->info($date);*/

        $this->info(view('notifications.email_notification', [
            'event_name' =>  123,
            'event_cover_url' => env('APP_URL') ,
            'event_session_url' => env('APP_URL') ,
            'event_session_date' => Carbon::now()->isoFormat('D MMMM в HH:mm мск'),
        ])->render());



        /*$this->info("Test started");

        Cache::store('redis');
        Cache::tags(['tag1', 'tag2'])->put('tag-test1', 'ok', 20);

        $this->info(Cache::tags(['tag1', 'tag2'])->get('tag-test1'));    //< 'ok' - use Cache::get('tag-test1') instead
        $this->info(Cache::tags(['tag2', 'tag1'])->get('tag-test1'));    //< 'ok' - use Cache::get('tag-test1') instead
        $this->info(Cache::tags(['tag1'])->get('tag-test1'));            //< 'ok' - use Cache::get('tag-test1') instead
        $this->info(Cache::tags(['tag2'])->get('tag-test1'));            //< 'ok' - use Cache::get('tag-test1') instead
        $this->info('--' . Cache::get('tag-test1'));                            //< 'ok'
        $this->info(Cache::forget('tag-test1'));                         //< deleted
        $this->info(Cache::tags(['tag1', 'tag2'])->forget('tag-test1')); //< deleted - use Cache::forget('tag-test1') instead
        $this->info(Cache::tags(['tag2', 'tag1'])->forget('tag-test1')); //< deleted - use Cache::forget('tag-test1') instead
        $this->info(Cache::tags(['tag1'])->forget('tag-test1'));         //< deleted - use Cache::forget('tag-test1') instead
        $this->info(Cache::tags(['tag2'])->forget('tag-test1'));         //< deleted - use Cache::forget('tag-test1') instead
        $this->info(Cache::tags(['tag1'])->flush());                     //< deleted all cache entries with tag 'tag1'
        $this->info(Cache::tags(['tag2', 'tag1'])->flush());             //< deleted all cache entries with tag 'tag2' or 'tag1'
        $this->info(Cache::tags(['tag2'])->flush());                     //< deleted all cache entries with tag 'tag2'
        $this->info(Cache::tags(['tag1', 'tag2'])->flush());             //< deleted all cache entries with tag 'tag1' or 'tag2'


        $this->info("Test finished");
*/
        return Command::SUCCESS;
    }
}
