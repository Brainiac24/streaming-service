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
        Schema::table('event_data_collections', function (Blueprint $table) {
            $table->foreignId('event_data_collection_template_id')->constrained(
                table: 'event_data_collection_templates', indexName: 'templates_event_data_collections_index'
            );
            $table->string('config_json')->nullable()->change();
            $table->renameColumn('config_json', 'value');
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropForeign('types_data_collection_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_data_collection_templates', function (Blueprint $table) {
            //
        });
    }
};
