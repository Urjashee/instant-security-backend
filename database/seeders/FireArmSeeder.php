<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FireArmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("firearms")->insert(["id" => "1", "name" => "F-01"]);
        DB::table("firearms")->insert(["id" => "2", "name" => "F-02"]);
        DB::table("firearms")->insert(["id" => "3", "name" => "F-03"]);
        DB::table("firearms")->insert(["id" => "4", "name" => "F-04"]);
        DB::table("firearms")->insert(["id" => "5", "name" => "F-05"]);
        DB::table("firearms")->insert(["id" => "6", "name" => "F-07"]);
        DB::table("firearms")->insert(["id" => "7", "name" => "T-89"]);
        DB::table("firearms")->insert(["id" => "8", "name" => "FLSD"]);
    }
}
