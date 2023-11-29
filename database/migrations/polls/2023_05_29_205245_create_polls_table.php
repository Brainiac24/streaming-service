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
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_session_id')->constrained();
            $table->string('question');
            $table->char('chanel', 32)->nullable();
            $table->char('private_chanel', 32)->nullable();
            $table->boolean('is_multiselect')->nullable();
            $table->boolean('is_public_results')->nullable();
            $table->foreignId('poll_type_id')->constrained();
            $table->foreignId('poll_status_id')->constrained();
            $table->timestamp('start_at')->nullable();
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
        Schema::dropIfExists('polls');
    }
};
