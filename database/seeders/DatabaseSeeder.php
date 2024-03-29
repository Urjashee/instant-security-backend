<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesSeeder::class);
        $this->call(StateSeeder::class);
        $this->call(FireArmSeeder::class);
        $this->call(UserTableSeeder::class);
//        $this->call(UserTableSeeder::class);
        // \App\Models\User::factory(10)->create();
    }
}
