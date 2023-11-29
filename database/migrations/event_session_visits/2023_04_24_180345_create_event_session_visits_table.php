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
        Schema::create('event_session_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_session_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('ip')->nullable();
            $table->string('url')->nullable();
            $table->string('useragent')->nullable();
            $table->string('source')->nullable();
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
        Schema::dropIfExists('event_session_visits');
    }
};
