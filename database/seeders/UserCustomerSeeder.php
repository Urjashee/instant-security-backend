<?php

namespace Database\Seeders;

use App\Models\Firearms;
use App\Models\State;
use Faker\Factory;
use Faker\Provider\Address;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $faker = Factory::create();
        $states = State::where("active", 1)->pluck('id')->toArray();
        $fire_arms = Firearms::all()->pluck('id')->toArray();
        for ($i = 11; $i <= 250; $i++) {
            $first_name = $faker->firstName;
            $last_name = $faker->lastName;
            $state_id = $faker->randomElement($states);
            DB::table("users")->insert([
                "first_name" => $first_name,
                "last_name" => $last_name,
                "friendly_name" => $i . "_" . $first_name . "_" . $last_name,
                "email" => $faker->email,
                "state_id" => $state_id,
                "phone_no" => $faker->phoneNumber,
                "email_verified_at" => "2022-12-03 14:24:04",
                "password" => Hash::make("goblin123"),
                "active" => "1",
                "status" => "1",
                "profile" => "1",
                "user_role_id" => 2,
            ]);
            DB::table("customer_profiles")->insert([
                "user_id" => $i,
                "address1" => $faker->streetAddress("New York"),
                "address2" => $faker->address("New York"),
                "city" => "New York",
                "zipcode" => Address::postcode("New York"),
                "profile_image" => "web_profile_images/1696589093.jpg",
                "state_id_image" => "web_state_id_images/1696589093.jpg",
            ]);
        }
    }
}
