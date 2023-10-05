<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name',120);
            $table->string('last_name',120);
            $table->string('friendly_name',120)->nullable(true);
            $table->string('email',120)->unique();
            $table->string('phone_no',20);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password',250);
            $table->boolean('active')->default(0);
            $table->unsignedBigInteger('user_role_id');
            $table->timestamps();

            $table->foreign('user_role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
