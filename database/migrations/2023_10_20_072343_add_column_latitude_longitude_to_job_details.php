<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLatitudeLongitudeToJobDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_details', function (Blueprint $table) {
            $table->double("clock_in_latitude")->nullable("true")->after("clock_in_request_accepted");
            $table->double("clock_in_longitude")->nullable("true")->after("clock_in_latitude");
            $table->double("clock_out_latitude")->nullable("true")->after("clock_out_request_accepted");
            $table->double("clock_out_longitude")->nullable("true")->after("clock_out_latitude");
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
