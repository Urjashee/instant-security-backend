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
            "activity_log_message" => "required",
            "activity_log_timestamp" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");

        $activity = new ActivityReport();
        $activity->job_id = $job_id;
        $activity->user_id = $request->input(Constants::CURRENT_USER_ID_KEY);
        $activity->message = $request->input("activity_log_message");
        $activity->timestamp = $request->input("activity_log_timestamp");
        if ($request->has("activity_log_image")) {
            $activityFileName = time() . '.' . $request->file("activity_log_image")->getClientOriginalExtension();
            $activity_log_image = $request->file("activity_log_image");
            $activity_log_image->storeAs('activity_log_image', $activityFileName, 's3');
            $activity->image = 'activity_log_image/' . $activityFileName;
        }
        $activity->save();
        return ResponseFormatter::successResponse("Activity log added");
    }

    public function getActivityReport($job_id,Request $request): \Illuminate\Http\JsonResponse
    {
        $contentData = array();
        $jobData = array();
        $s3SiteName = Config::get('constants.s3_bucket');
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");

        $activityReports = ActivityReport::where("job_id",$job_id)->get();
        if ($activityReports) {
            foreach ($activityReports as $activityReport) {
                $jobData[] = [
                    "job_id" => $activityReport->job_id,
                    "user_id" => $activityReport->user_id,
                    "activity_message" => $activityReport->message,
                    "activity_timestamp" => (string)$activityReport->timestamp,
                    "activity_image" => $activityReport->image == null ? "" : $s3SiteName . $activityReport->image,
                ];
                $contentData[] = $jobData;
            }
            return ResponseFormatter::successResponse("Activity report", $contentData);
        } else {
            return ResponseFormatter::errorResponse("No activity report");
        }
    }
}
