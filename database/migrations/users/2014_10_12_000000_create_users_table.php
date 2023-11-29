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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('login')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('name')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_verified')->default(0);
            $table->char('country_code', 8)->nullable();
            $table->char('phone_code', 8)->nullable();
            $table->char('phone', 32)->nullable();
            $table->string('channel', 32)->nullable();
            $table->longText('config_json')->nullable();
            $table->decimal('balance', 16, 6)->default(0);
            $table->char('lang', 8)->nullable();
            $table->text('avatar_path')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('work_scope')->nullable();
            $table->string('work_company')->nullable();
            $table->string('work_division')->nullable();
            $table->string('work_position')->nullable();
            $table->string('education')->nullable();
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
        Schema::dropIfExists('users');
    }
};
