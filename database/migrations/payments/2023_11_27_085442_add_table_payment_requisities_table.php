<?php

use App\Enums\Payment\PaymentRequisitesServiceEnum;
use App\Enums\Payment\PaymentRequisitesStatusEnum;
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
    public function up(): void
    {
        Schema::create('payment_requisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained();
            $table->tinyInteger('status')->default(PaymentRequisitesStatusEnum::ACTIVE->value);
            $table->tinyInteger('service')->default(PaymentRequisitesServiceEnum::CLOUD_PAYMENTS->value);
            $table->string('public_api_key');
            $table->string('private_api_key');
            $table->json('data_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requisites');
    }
};
