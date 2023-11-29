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
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_ticket_sales_enabled')->default(0)->after('is_data_collection_enabled');;
            $table->decimal('ticket_price', 16, 6)->nullable()->after('is_ticket_sales_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_ticket_sales_enabled');
            $table->dropColumn('ticket_price');
        });
    }
};
