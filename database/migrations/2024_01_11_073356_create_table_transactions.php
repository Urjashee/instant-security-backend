<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('guard_id');
            $table->unsignedBigInteger('job_id');
            $table->boolean("status")->default(0)->nullable(false);
            $table->date("transaction_date")->nullable(false);
            $table->float("amount_to_guard", 6, 2)->nullable(false);
            $table->float("amount_to_app", 6,2)->nullable(false);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('users');
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
        Schema::dropIfExists('transactions');
    }
}
