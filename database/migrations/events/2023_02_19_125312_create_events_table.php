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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('project_id')->constrained();
            $table->foreignId('fare_id')->constrained();
            $table->foreignId('access_group_id')->constrained();
            $table->text('cover_img_path')->nullable();
            $table->text('logo_img_path')->nullable();
            $table->text('description')->nullable();
            $table->string('link')->unique()->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->longText('config_json')->nullable();
            $table->foreignId('event_status_id')->constrained();
            $table->boolean('is_unique_ticket_enabled')->default(false)->nullable();
            $table->boolean('is_multi_ticket_enabled')->default(false)->nullable();
            $table->boolean('is_data_collection_enabled')->default(false)->nullable();
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
        Schema::dropIfExists('events');
    }
};
