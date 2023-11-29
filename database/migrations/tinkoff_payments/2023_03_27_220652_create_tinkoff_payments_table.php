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
        Schema::create('tinkoff_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->bigInteger('response_payment_id')->nullable();
            $table->string('response_payment_url')->nullable();
            $table->foreignId('tinkoff_payment_status_id')->constrained();
            $table->longText('request')->nullable();
            $table->longText('response')->nullable();
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
        Schema::dropIfExists('tinkoff_payments');
    }
};
