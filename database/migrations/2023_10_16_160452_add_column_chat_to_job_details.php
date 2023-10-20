<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnChatToJobDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_details', function (Blueprint $table) {
            $table->string("chat_sid")->nullable("true")->after("clock_out_request_accepted");
            $table->string("participant_id")->nullable("true")->after("chat_sid");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_details', function (Blueprint $table) {
            //
        });
    }
}
