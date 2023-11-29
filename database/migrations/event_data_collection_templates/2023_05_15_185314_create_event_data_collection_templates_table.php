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
        Schema::create('event_data_collection_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained();
            $table->string('name');
            $table->string('label')->nullable();
            $table->boolean('is_required')->default(0);
            $table->boolean('is_editable')->default(1);
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
        Schema::dropIfExists('event_data_collection_templates');
    }
};
