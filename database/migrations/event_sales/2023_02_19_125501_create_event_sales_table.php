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
        Schema::create('event_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained();
            $table->foreignId('event_session_id')->constrained();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('button_text')->nullable();
            $table->string('url')->nullable();
            $table->string('cover_img_path')->nullable();
            $table->text('style')->nullable();
            $table->foreignId('event_sale_status_id')->constrained();
            $table->integer('clicks_count')->default(0);
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
        Schema::dropIfExists('event_sales');
    }
};
