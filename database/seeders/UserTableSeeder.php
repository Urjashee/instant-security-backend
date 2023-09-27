<?php

namespace Database\Seeders;

use App\Models\Roles;
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $faker = Factory::create();
        // following line retrieve all the user_ids from DB
        $roles = Roles::all()->pluck('id')->toArray();
        for ($i = 1; $i < 300; $i++) {
            $first_name = $faker->firstName;
            $last_name = $faker->lastName;
            $role_user_id = $faker->randomElement($roles);
            DB::table("users")->insert([
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email" => $faker->email,
                "email_verified_at" => "2022-12-03 14:24:04",
                "password" => Hash::make("goblin123"),
                "active" => "1",
                "user_role_id" => $role_user_id,
            ]);
        }
    }
}
