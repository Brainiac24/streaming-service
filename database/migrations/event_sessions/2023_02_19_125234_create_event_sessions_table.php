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
        Schema::create('event_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained();
            $table->string('name')->nullable();
            $table->text('content')->nullable();
            $table->longtext('config_json')->nullable();
            $table->foreignId('event_session_status_id')->constrained();
            $table->foreignId('stream_id')->constrained();
            $table->foreignId('event_session_id')->constrained();
            $table->double('sort')->nullable();
            $table->foreignId('fare_id')->constrained();
            $table->string('code')->nullable();
            $table->boolean('has_interactive_block')->nullable();
            $table->text('logo_img_path')->nullable();
            $table->string('key')->nullable();
            $table->char('chanel', 32)->nullable();
            $table->char('private_chanel', 32)->nullable();
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
        Schema::dropIfExists('event_sessions');
    }
};
