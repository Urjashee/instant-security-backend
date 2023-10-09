<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableJobActivityLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_activity_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->text("report");
            $table->text("image");
            $table->timestamps();

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
        Schema::dropIfExists('job_activity_log');
    }
}
