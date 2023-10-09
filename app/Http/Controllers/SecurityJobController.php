<?php

namespace App\Http\Controllers;

use App\Common\ResponseFormatter;
use App\Constants;
use App\Models\FireGuardLicense;
use App\Models\JobFireLicense;
use App\Models\JobType;
use App\Models\SecurityJob;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SecurityJobController extends Controller
{
    public function addJobs(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "state_id" => "required",
            "job_type_id" => "required",
            "event_name" => "required",
            "street1" => "required",
            "street2" => "required",
            "city" => "required",
            "zipcode" => "required",
            "event_start" => "required",
            "event_end" => "required",
            "osha_license_id" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        if (!State::where("id", $request->input("state_id"))
            ->where("active",1)->first())
            return ResponseFormatter::successResponse("Not a valid state_id");

        if (!JobType::where("id", $request->input("state_id"))->first())
            return ResponseFormatter::successResponse("Not a valid job_type_id");

        $jobType = JobType::where("id",$request->input("job_type_id"))->first();

        if ($jobType) {
            $newJobs = new SecurityJob();
            $newJobs->state_id = $request->input("state_id");
            $newJobs->user_id = $request->input(Constants::CURRENT_USER_ID_KEY);
            $newJobs->job_type_id = $request->input("job_type_id");
            $newJobs->event_name = $request->input("event_name");
            $newJobs->street1 = $request->input("street1");
            $newJobs->street2 = $request->input("street2");
            $newJobs->city = $request->input("city");
            $newJobs->zipcode = $request->input("zipcode");
            $newJobs->event_start = $request->input("event_start");
            $newJobs->event_end = $request->input("event_end");
            $newJobs->osha_license_id = $request->input("osha_license_id");
            $newJobs->job_description = $request->input("job_description");
            $newJobs->price = $jobType->hourly_rate;
            $difference = $request->input("event_end") - $request->input("event_start");
            $newJobs->max_price = ($difference/3600) * 10;
            $newJobs->price_paid = 0;
            $newJobs->job_status = 1;
            $newJobs->save();
            $newJobs->refresh();
            if ($request->has("fire_guard_license")) {
                $fireGuardLicenses = json_decode($request->input('fire_guard_license'));
                foreach ($fireGuardLicenses as $fireGuardLicense) {
                    $fireGuard = new JobFireLicense();
                    $fireGuard->job_id = $newJobs->id;
                    $fireGuard->fire_guard_license_id = $fireGuardLicense;
                    $fireGuard->save();
                }
            }


            return ResponseFormatter::successResponse("Job successfully added!");
        } else {
            return ResponseFormatter::errorResponse("No such Job type");
        }
    }
}


