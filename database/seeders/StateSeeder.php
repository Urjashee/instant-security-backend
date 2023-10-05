<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("states")->insert(["id" => "1", "name" => "Alabama", "abbreviation" => "AL", "active" => 0]);
        DB::table("states")->insert(["id" => "2", "name" => "Alaska", "abbreviation" => "AK", "active" => 0]);
        DB::table("states")->insert(["id" => "3", "name" => "American Samoa", "abbreviation" => "AS", "active" => 0]);
        DB::table("states")->insert(["id" => "4", "name" => "Arizona", "abbreviation" => "AZ", "active" => 0]);
        DB::table("states")->insert(["id" => "5", "name" => "Arkansas", "abbreviation" => "AR", "active" => 0]);
        DB::table("states")->insert(["id" => "6", "name" => "California", "abbreviation" => "CA", "active" => 0]);
        DB::table("states")->insert(["id" => "7", "name" => "Colorado", "abbreviation" => "CO", "active" => 0]);
        DB::table("states")->insert(["id" => "8", "name" => "Connecticut", "abbreviation" => "CT", "active" => 0]);
        DB::table("states")->insert(["id" => "9", "name" => "Delaware", "abbreviation" => "DE", "active" => 0]);
        DB::table("states")->insert(["id" => "10", "name" => "District Of Columbia", "abbreviation" => "DC", "active" => 0]);
        DB::table("states")->insert(["id" => "11", "name" => "Federated States Of Micronesia", "abbreviation" => "FM", "active" => 0]);
        DB::table("states")->insert(["id" => "12", "name" => "Florida", "abbreviation" => "FL", "active" => 0]);
        DB::table("states")->insert(["id" => "13", "name" => "Georgia", "abbreviation" => "GA", "active" => 0]);
        DB::table("states")->insert(["id" => "14", "name" => "Guam", "abbreviation" => "GU", "active" => 0]);
        DB::table("states")->insert(["id" => "15", "name" => "Hawaii", "abbreviation" => "HI", "active" => 0]);
        DB::table("states")->insert(["id" => "16", "name" => "Idaho", "abbreviation" => "ID", "active" => 0]);
        DB::table("states")->insert(["id" => "17", "name" => "Illinois", "abbreviation" => "IL", "active" => 0]);
        DB::table("states")->insert(["id" => "18", "name" => "Indiana", "abbreviation" => "IN", "active" => 0]);
        DB::table("states")->insert(["id" => "19", "name" => "Iowa", "abbreviation" => "IA", "active" => 0]);
        DB::table("states")->insert(["id" => "20", "name" => "Kansas", "abbreviation" => "KS", "active" => 0]);
        DB::table("states")->insert(["id" => "21", "name" => "Kentucky", "abbreviation" => "KY", "active" => 0]);
        DB::table("states")->insert(["id" => "22", "name" => "Louisiana", "abbreviation" => "LA", "active" => 0]);
        DB::table("states")->insert(["id" => "23", "name" => "Maine", "abbreviation" => "ME", "active" => 0]);
        DB::table("states")->insert(["id" => "24", "name" => "Marshall Islands", "abbreviation" => "MH", "active" => 0]);
        DB::table("states")->insert(["id" => "25", "name" => "Maryland", "abbreviation" => "MD", "active" => 0]);
        DB::table("states")->insert(["id" => "26", "name" => "Massachusetts", "abbreviation" => "MA", "active" => 0]);
        DB::table("states")->insert(["id" => "27", "name" => "Michigan", "abbreviation" => "MI", "active" => 0]);
        DB::table("states")->insert(["id" => "28", "name" => "Minnesota", "abbreviation" => "MN", "active" => 0]);
        DB::table("states")->insert(["id" => "29", "name" => "Mississippi", "abbreviation" => "MS", "active" => 0]);
        DB::table("states")->insert(["id" => "30", "name" => "Missouri", "abbreviation" => "MO", "active" => 0]);
        DB::table("states")->insert(["id" => "31", "name" => "Montana", "abbreviation" => "MT", "active" => 0]);
        DB::table("states")->insert(["id" => "32", "name" => "Nebraska", "abbreviation" => "NE", "active" => 0]);
        DB::table("states")->insert(["id" => "33", "name" => "Nevada", "abbreviation" => "NV", "active" => 0]);
        DB::table("states")->insert(["id" => "34", "name" => "New Hampshire", "abbreviation" => "NH", "active" => 0]);
        DB::table("states")->insert(["id" => "35", "name" => "New Jersey", "abbreviation" => "NJ", "active" => 1]);
        DB::table("states")->insert(["id" => "36", "name" => "New Mexico", "abbreviation" => "NM", "active" => 0]);
        DB::table("states")->insert(["id" => "37", "name" => "New York", "abbreviation" => "NY", "active" => 1]);
        DB::table("states")->insert(["id" => "38", "name" => "North Carolina", "abbreviation" => "NC", "active" => 0]);
        DB::table("states")->insert(["id" => "39", "name" => "North Dakota", "abbreviation" => "ND", "active" => 0]);
        DB::table("states")->insert(["id" => "40", "name" => "Northern Mariana Islands", "abbreviation" => "MP", "active" => 0]);
        DB::table("states")->insert(["id" => "41", "name" => "Ohio", "abbreviation" => "OH", "active" => 0]);
        DB::table("states")->insert(["id" => "42", "name" => "Oklahoma", "abbreviation" => "OK", "active" => 0]);
        DB::table("states")->insert(["id" => "43", "name" => "Oregon", "abbreviation" => "OR", "active" => 0]);
        DB::table("states")->insert(["id" => "44", "name" => "Palau", "abbreviation" => "PW", "active" => 0]);
        DB::table("states")->insert(["id" => "45", "name" => "Pennsylvania", "abbreviation" => "PA", "active" => 0]);
        DB::table("states")->insert(["id" => "46", "name" => "Puerto Rico", "abbreviation" => "PR", "active" => 0]);
        DB::table("states")->insert(["id" => "47", "name" => "Rhode Island", "abbreviation" => "RI", "active" => 0]);
        DB::table("states")->insert(["id" => "48", "name" => "South Carolina", "abbreviation" => "SC", "active" => 0]);
        DB::table("states")->insert(["id" => "49", "name" => "South Dakota", "abbreviation" => "SD", "active" => 0]);
        DB::table("states")->insert(["id" => "50", "name" => "Tennessee", "abbreviation" => "TN", "active" => 0]);
        DB::table("states")->insert(["id" => "51", "name" => "Texas", "abbreviation" => "TX", "active" => 0]);
        DB::table("states")->insert(["id" => "52", "name" => "Utah", "abbreviation" => "UT", "active" => 0]);
        DB::table("states")->insert(["id" => "53", "name" => "Vermont", "abbreviation" => "VT", "active" => 0]);
        DB::table("states")->insert(["id" => "54", "name" => "Virgin Islands", "abbreviation" => "VI", "active" => 0]);
        DB::table("states")->insert(["id" => "55", "name" => "Virginia", "abbreviation" => "VA", "active" => 0]);
        DB::table("states")->insert(["id" => "56", "name" => "Washington", "abbreviation" => "WA", "active" => 0]);
        DB::table("states")->insert(["id" => "57", "name" => "West Virginia", "abbreviation" => "WV", "active" => 0]);
        DB::table("states")->insert(["id" => "58", "name" => "Wisconsin", "abbreviation" => "WI", "active" => 0]);
        DB::table("states")->insert(["id" => "59", "name" => "Wyoming", "abbreviation" => "WY", "active" => 0]);
    }
}
