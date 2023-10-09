<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableJobDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guard_id');
            $table->unsignedBigInteger('job_id');
            $table->boolean('clock_in_request')->default(0);
            $table->time('clock_in_time')->nullable(true);
            $table->boolean('clock_in_request_accepted')->default(0);
            $table->boolean('clock_out_request')->default(0);
            $table->time('clock_out_time')->nullable(true);
            $table->boolean('clock_out_request_accepted')->default(0);
            $table->timestamps();

            $table->foreign('guard_id')->references('id')->on('users');
            $table->foreign('job_id')->references('id')->on('security_jobs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_details');
    }
}
