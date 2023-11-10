<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\JobFunctions;
use App\Common\ResponseFormatter;
use App\Constants;
use App\Models\ActivityReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class ActivityReportController extends Controller
{
    public function addActivityReport(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "message" => "required",
            "timestamp" => "required",
            "activity_log_image" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");

        $activity = new ActivityReport();
        $activity->job_id = $job_id;
        $activity->user_id = $request->input(Constants::CURRENT_USER_ID_KEY);
        $activity->message = $request->input("message");
        $activity->timestamp = $request->input("timestamp");
        $activityFileName = time() . '.' . $request->file("activity_log_image")->getClientOriginalExtension();
        $activity_log_image = $request->file("activity_log_image");
        $activity_log_image->storeAs('activity_log_image', $activityFileName, 's3');
        $activity->image = 'activity_log_image/' . $activityFileName;
        $activity->save();
        return ResponseFormatter::successResponse("Activity log added");
    }

    public function getActivityReport($job_id,Request $request): \Illuminate\Http\JsonResponse
    {
        $s3SiteName = Config::get('constants.s3_bucket');
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");

        $activityReport = ActivityReport::where("job_id",$job_id)->get();
        if ($activityReport) {
            return ResponseFormatter::successResponse("Activity report", $activityReport);
        } else {
            return ResponseFormatter::errorResponse("No activity report");
        }
    }
}
