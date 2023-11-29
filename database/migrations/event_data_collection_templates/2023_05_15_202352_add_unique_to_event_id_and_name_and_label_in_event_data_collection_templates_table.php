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
        Schema::table('event_data_collection_templates', function (Blueprint $table) {
            $table->unique(['event_id', 'name']);
            $table->unique(['event_id', 'label']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_data_collection_templates', function (Blueprint $table) {
            //
        });
    }
};
