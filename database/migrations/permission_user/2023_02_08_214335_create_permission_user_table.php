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
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('access_group_id')->nullable()->constrained();
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'permission_id', 'access_group_id'], 'user_permission_access_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_user');
    }
};
