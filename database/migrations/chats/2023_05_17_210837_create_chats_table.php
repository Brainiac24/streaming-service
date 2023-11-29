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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_session_id')->constrained();
            $table->string('channel');
            $table->boolean('is_messages_enabled')->default(1);
            $table->boolean('is_questions_enabled')->nullable();
            $table->boolean('is_question_messages_enabled')->nullable();
            $table->boolean('is_question_moderation_enabled')->nullable();
            $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('chats');
    }
};
