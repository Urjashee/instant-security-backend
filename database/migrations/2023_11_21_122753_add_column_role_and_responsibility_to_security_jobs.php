<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnRoleAndResponsibilityToSecurityJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('security_jobs', function (Blueprint $table) {
            $table->text("roles_and_responsibility")->nullable(false)->after("job_description");
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
