<?php

namespace Database\Seeders;

use App\Models\Firearms;
use App\Models\JobType;
use App\Models\State;
use App\Models\User;
use Faker\Factory;
use Faker\Provider\Address;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobsSeeder extends Seeder
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
        $users = User::where("user_role_id", 2)->pluck('id')->toArray();
        $states = State::where("active", 1)->pluck('id')->toArray();
        $job_types = JobType::all()->pluck('id')->toArray();
        $fire_arms = Firearms::all()->pluck('id')->toArray();
        for ($i = 1; $i <= 500; $i++) {
            $user_id = $faker->randomElement($users);
            $state_id = $faker->randomElement($states);
            $job_type_id = $faker->randomElement($job_types);
            DB::table("security_jobs")->insert([
                "user_id" => $user_id,
                "state_id" => $state_id,
                "job_type_id" => $job_type_id,
                "event_name" => $faker->word(2),
                "street1" => $faker->streetAddress("New York"),
                "street2" => $faker->address("New York"),
                "city" => "New York",
                "zipcode" => Address::postcode("New York"),
                "event_start" => 1696814615,
                "event_end" => 1696847015,
                "osha_license_id" => 2,
                "job_description" => $faker->text(300),
                "roles_and_responsibility" => $faker->text(300),
                "price" => 10.0,
                "max_price" => 90.0,
                "price_paid" => 0,
                "job_status" => 1,
            ]);
            for ($j = 1; $j <= 5; $j++) {
                $fire_arms_id = $faker->randomElement($fire_arms);
                DB::table('job_fire_license')->insert([
                    "job_id" => $i,
                    "fire_guard_license_id" => $fire_arms_id,
                ]);
            }
        }
    }
}
