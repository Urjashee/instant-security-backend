<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStateLicenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('state_licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('state_id');
            $table->text('security_guard_license_image');
            $table->date('security_guard_license_expiry');
            $table->text('fire_guard_license_type')->nullable(true);
            $table->text('fire_guard_license_image')->nullable(true);
            $table->date('fire_guard_license_expiry')->nullable(true);
            $table->text('cpr_certificate_image');
            $table->date('cpr_certificate_expiry')->nullable(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('state_id')->references('id')->on('states');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('state_licenses');
    }
}
