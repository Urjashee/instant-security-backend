<?php

namespace Database\Seeders;

use App\Models\Roles;
use App\Models\State;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        $states = State::where("active", 1)->pluck('id')->toArray();
        $state_id = $faker->randomElement($states);
        DB::table("users")->insert([
            "email" => "superadmin@gmail.com",
            "email_verified_at" => "2022-12-03 14:24:04",
            "password" => Hash::make("super_admin"),
            "first_name" => "Super",
            "last_name" => "Admin",
            "state_id" => $state_id,
            "phone_no" => $faker->phoneNumber,
            "active" => "1",
            "status" => "1",
            "profile" => "1",
            "user_role_id" => 1,
        ]);
    }
}
