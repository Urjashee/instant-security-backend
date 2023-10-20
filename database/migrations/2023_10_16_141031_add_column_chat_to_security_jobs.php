<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnChatToSecurityJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('security_jobs', function (Blueprint $table) {
            $table->string("chat_sid")->nullable("true")->after("additional_hours_accepted");
            $table->string("chat_service_sid")->nullable("true")->after("chat_sid");
            $table->string("participant_id")->nullable("true")->after("chat_service_sid");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('security_jobs', function (Blueprint $table) {
            //
        });
    }
}
