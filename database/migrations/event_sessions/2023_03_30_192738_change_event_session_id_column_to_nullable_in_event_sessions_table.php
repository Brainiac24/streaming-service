<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_sessions', function (Blueprint $table) {
            $table->foreignId('event_session_id')->nullable()->change();
            $table->renameColumn('chanel', 'channel');
            $table->renameColumn('private_chanel', 'private_channel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_sessions', function (Blueprint $table) {
            //
        });
    }
};
