<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmenitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("amenities")->insert(["id" => "1", "name" => "Cardio Machines"]);
        DB::table("amenities")->insert(["id" => "2", "name" => "Free Weights"]);
        DB::table("amenities")->insert(["id" => "3", "name" => "Machine Weights"]);
        DB::table("amenities")->insert(["id" => "4", "name" => "Cycle Studio"]);
        DB::table("amenities")->insert(["id" => "5", "name" => "Multipurpose Studio"]);
        DB::table("amenities")->insert(["id" => "6", "name" => "Showers"]);
        DB::table("amenities")->insert(["id" => "7", "name" => "Lap Pool"]);
        DB::table("amenities")->insert(["id" => "8", "name" => "Leisure Pool"]);
        DB::table("amenities")->insert(["id" => "9", "name" => "Basketball Court"]);
        DB::table("amenities")->insert(["id" => "10", "name" => "Tennis Court"]);
        DB::table("amenities")->insert(["id" => "11", "name" => "Racquetball Court"]);
        DB::table("amenities")->insert(["id" => "12", "name" => "Volleyball Court"]);
        DB::table("amenities")->insert(["id" => "13", "name" => "Boxing Equipment"]);
        DB::table("amenities")->insert(["id" => "14", "name" => "Indoor Track"]);
        DB::table("amenities")->insert(["id" => "15", "name" => "Sauna(s)"]);
        DB::table("amenities")->insert(["id" => "16", "name" => "Tanning Beds"]);
        DB::table("amenities")->insert(["id" => "17", "name" => "Cryotherapy"]);
        DB::table("amenities")->insert(["id" => "18", "name" => "Turf Area"]);
        DB::table("amenities")->insert(["id" => "19", "name" => "Childcare Facilities"]);
        DB::table("amenities")->insert(["id" => "20", "name" => "Snack Bar/Kitchen"]);
        DB::table("amenities")->insert(["id" => "21", "name" => "Women’s Training Studio"]);
    }
}
