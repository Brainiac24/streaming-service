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
        Schema::table('event_sale_stats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stream_id');
            $table->dropColumn('stream_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_sale_stats', function (Blueprint $table) {
            //
        });
    }
};
