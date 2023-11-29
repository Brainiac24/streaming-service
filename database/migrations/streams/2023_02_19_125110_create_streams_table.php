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
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('img_path')->nullable();
            $table->foreignId('event_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->dateTime('date')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('last_auth_at')->nullable();
            $table->integer('user_connected_count')->nullable();
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->foreignId('stream_status_id')->constrained();
            $table->boolean('is_onair')->nullable();
            $table->boolean('is_dvr_enabled')->nullable();
            $table->boolean('is_dvr_out')->nullable();
            $table->boolean('is_fullhd')->nullable();
            $table->string('key', 64)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('streams');
    }
};
