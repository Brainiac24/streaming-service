<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table("streams")->update([
            "date_at" => NULL,
            "start_at" => NULL,
            "last_auth_at" => NULL
        ]);

        Schema::table('streams', function (Blueprint $table) {
            $table->timestamp('date_at')->nullable()->default(null)->change();
            $table->timestamp('start_at')->nullable()->default(null)->change();
            $table->timestamp('last_auth_at')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('streams', function (Blueprint $table) {
            //
        });
    }
};
