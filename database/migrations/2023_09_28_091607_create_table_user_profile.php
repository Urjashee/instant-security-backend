<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUserProfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('address1');
            $table->text('address2')->nullable(true);
            $table->string('state');
            $table->text('city');
            $table->text('zipcode');
            $table->text('profile_image')->nullable(true);
            $table->text('ssc_image')->nullable(true);
            $table->text('govt_id_image')->nullable(true);
            $table->text('govt_id_expiry_date')->nullable(true);
            $table->text('osha_license_type')->nullable(true);
            $table->text('osha_license_image')->nullable(true);
            $table->text('osha_license_expiry_date')->nullable(true);
            $table->text('account_number')->nullable(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}
