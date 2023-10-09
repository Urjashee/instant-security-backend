<?php

namespace Database\Seeders;

use App\Models\Firearms;
use App\Models\State;
use Faker\Factory;
use Faker\Provider\Address;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
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
        for ($i = 2; $i <= 120; $i++) {
            $first_name = $faker->firstName;
            $last_name = $faker->lastName;
            $state_id = $faker->randomElement($states);
            $fire_arms_id = $faker->randomElement($fire_arms);
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
                "user_role_id" => 3,
            ]);
            DB::table("user_profiles")->insert([
                "user_id" => $i,
                "address1" => $faker->streetAddress("New York"),
                "address2" => $faker->address("New York"),
                "city" => "New York",
                "zipcode" => Address::postcode("New York"),
                "profile_image" => "user_profile_image/1696579924.png",
                "ssc_image" => "user_ssc_image/1696579927.jpg",
                "govt_id_image" => "user_govt_id_image/1696579927.jpg",
                "govt_id_expiry_date" => "2023-10-30",
                "account_number" => $faker->numberBetween(1000000000, 9999999999),
                "routing" => $faker->numberBetween(1000000000, 9999999999),
                "bank_name" => $faker->text(6),
                "terms_and_condition" => 1,
            ]);
            DB::table("fire_guard_licenses")->insert([
                "user_id" => $i,
                "state_id" => $faker->randomElement($states),
                "fire_guard_license_type" => $fire_arms_id,
                "fire_guard_license_image" => "fire_guard_license_image/1696584645.jpg",
                "fire_guard_license_expiry" => "2023-10-30",
            ]);
        }
    }

}
