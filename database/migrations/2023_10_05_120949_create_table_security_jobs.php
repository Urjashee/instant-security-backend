<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSecurityJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('job_type_id');
            $table->string("event_name",250);
            $table->text("street1");
            $table->text("street2")->nullable(true);
            $table->string("city",100);
            $table->string("zipcode",10);
            $table->integer("event_start");
            $table->integer("event_end");
            $table->integer("osha_license_id")->nullable();
            $table->text("job_description");
            $table->float("price", 5,2);
            $table->float("max_price", 6,2);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('state_id')->references('id')->on('states');
            $table->foreign('job_type_id')->references('id')->on('job_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('security_jobs');
    }
}
