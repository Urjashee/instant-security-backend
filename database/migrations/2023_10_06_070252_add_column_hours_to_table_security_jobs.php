<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnHoursToTableSecurityJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('security_jobs', function (Blueprint $table) {
            $table->integer("total_price")->nullable("true")->after("max_price");
            $table->boolean("price_paid")->after("total_price");
            $table->integer("job_status")->after("price_paid");
            $table->boolean("additional_hour_request")->default(0)->after("job_status");
            $table->integer("additional_hours")->nullable("true")->after("additional_hour_request");
            $table->boolean("additional_hours_accepted")->default(0)->after("additional_hours");
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
