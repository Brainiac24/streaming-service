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
        Schema::create('tinkoff_payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->longText('request')->nullable();
            $table->bigInteger('response_payment_id')->nullable();
            $table->foreignId('tinkoff_payment_id')->constrained();
            $table->foreignId('tinkoff_payment_status_id')->constrained(
                table: 'tinkoff_payment_statuses', indexName: 'statuses_tinkoff_payment_notifications_index'
            );
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
        Schema::dropIfExists('tinkoff_payment_notifications');
    }
};
