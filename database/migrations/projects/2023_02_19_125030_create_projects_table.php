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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->text('cover_img_path')->nullable();
            $table->foreignId('project_status_id')->constrained();
            $table->string('support_name')->nullable();
            $table->string('support_link')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_site')->nullable();
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
        Schema::dropIfExists('projects');
    }
};
