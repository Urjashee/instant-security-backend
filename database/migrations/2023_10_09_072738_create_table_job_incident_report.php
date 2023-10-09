<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableJobIncidentReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_incident_report', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->string("name",250);
            $table->text("description");
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
        Schema::dropIfExists('job_incident_report');
    }
}
