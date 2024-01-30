<?php

namespace App\Http\Controllers;

use App\Common\ResponseFormatter;
use App\Constants;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobTypeController extends Controller
{
    public function addJobType(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "state_id" => "required",
            "hourly_rate" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        $job_type = new JobType();
        $job_type->state_id = $request->input("state_id");
        $job_type->name = $request->input("name");
        $job_type->hourly_rate = $request->input("hourly_rate");
        $job_type->save();

        return ResponseFormatter::successResponse("Job type successfully added");
    }

    public function editJobType(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "state_id" => "required",
            "hourly_rate" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        $job_type = JobType::where("id", $id)->first();
        if ($job_type) {
            $job_type->state_id = $request->input("state_id");
            $job_type->name = $request->input("name");
            $job_type->hourly_rate = $request->input("hourly_rate");
            $job_type->update();

            return ResponseFormatter::successResponse("Job type successfully updated");
        } else {
            return ResponseFormatter::errorResponse("No such Job type");
        }
    }

    public function getJobType($id): \Illuminate\Http\JsonResponse
    {
        $job_type = JobType::where("id", $id)->first();

        if ($job_type) {
            return ResponseFormatter::successResponse("Job type", $job_type);
        } else {
            return ResponseFormatter::errorResponse("No such Job type");
        }
    }

    public function getAllJobTypes(Request $request): \Illuminate\Http\JsonResponse
    {
        $job_types = JobType::all();

        if ($job_types) {
            return ResponseFormatter::successResponse("Job types", $job_types);
        } else {
            return ResponseFormatter::errorResponse("No such Job type");
        }
    }
}
