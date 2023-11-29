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
        Schema::table('mailings', function (Blueprint $table) {
            $table->foreignId('mailing_requisite_id')->nullable()->constrained();
            $table->foreignId('message_template_id')->constrained();
            $table->foreignId('event_id')->constrained();
            $table->foreignId('event_session_id')->constrained();
            $table->foreignId('contact_group_id')->constrained();
            $table->boolean('is_default')->after('contact_group_id')->default(0);
            $table->timestamp('send_at')->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mailings', function (Blueprint $table) {
            //
        });
    }
};
