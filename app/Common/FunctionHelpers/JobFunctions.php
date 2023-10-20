<?php

namespace App\Common\FunctionHelpers;


use App\Common\ConfigList;
use App\Models\ActivityReport;
use App\Models\IncidentReport;
use App\Models\JobDetail;
use App\Models\SecurityJob;
use Illuminate\Support\Facades\Config;

class JobFunctions
{
    public static function authenticateUser($job_id, $user, $type)
    {
        if ($type == 1) {
            $job = JobDetail::where("guard_id", $user)
                ->where("job_id", $job_id)
                ->first();
            if ($job) {
                return (true);
            } else {
                return (false);
            }
        }
        if ($type == 2) {
            $job = SecurityJob::where("user_id", $user)
                ->where("id", $job_id)
                ->first();
            if ($job) {
                return (true);
            } else {
                return (false);
            }
        }
    }

    public static function jobDetails($job, $role, $status): array
    {
        $s3SiteName = Config::get('constants.s3_bucket');
        $activity_logs_data = array();
        $incident_report_data = array();
        $content_data = [
            "job_id" => $job->id,
            "job_event_name" => $job->event_name,
            "job_description" => $job->job_description,
            "job_type_id" => $job->job_type_id,
            "job_type" => $job->job_type->name,
            "job_state_id" => $job->state_id,
            "job_state" => $job->state->name,
            "job_date_time" => $job->event_start,
            "job_price" => $job->price,
            "job_max_price" => $job->max_price,
            "job_start_time" => $job->event_start,
            "job_end_time" => $job->event_end,
            "job_address" => $job->street1 . ", " . $job->street2 . ", " . $job->city . ", " . $job->state->name . ", " . $job->zipcode,
            "additional_hour_request" => !($job->additional_hour_request == 0),
            "additional_hours" => $job->additional_hours == null ? 0 : $job->additional_hours,
            "additional_hours_accepted" => !($job->additional_hours_accepted == 0),
        ];
        if ($status == 0) {
            $content_data += [
                "job_status_id" => $job->job_status,
                "job_status_name" => ConfigList::jobType($job->job_status),
            ];
        }
        if ($status == 1) {
            if ($job->security_jobs->clock_in_request == 1 && $job->security_jobs->clock_in_request_accepted == 1) {
                $content_data += [
                    "job_status_id" => 4,
                    "job_status_name" => ConfigList::jobType(4),
                ];
            } else {
                $content_data += [
                    "job_status_id" => 1,
                    "job_status_name" => ConfigList::jobType(1),
                ];
            }
        }
        if ($status == 2) {
            $content_data += [
                "job_status_id" => $job->job_status,
                "job_status_name" => ConfigList::jobType($job->job_status),
            ];
        }
        if ($status == 3) {
            $content_data += [
                "job_status_id" => $job->job_status,
                "job_status_name" => ConfigList::jobType($job->job_status),
            ];
        }
        if ($job->job_status == 1 || $job->job_status == 2) {
            $content_data += [
                "clock_in_request" => $job->security_jobs->clock_in_request == 0 ? FALSE : TRUE,
                "clock_in_request_accepted" => $job->security_jobs->clock_in_request_accepted == 0 ? FALSE : TRUE,
                "clock_out_request" => $job->security_jobs->clock_out_request == 0 ? FALSE : TRUE,
                "clock_out_request_accepted" => $job->security_jobs->clock_out_request_accepted == 0 ? FALSE : TRUE,
            ];
        }
        if ($role != 2) {
            $content_data += [
                "job_customer_id" => $job->user_id,
                "job_customer_name" => $job->users->first_name . " " . $job->users->last_name,
            ];
        }
        if ($role == 1) {
            $content_data += [
                "job_price_paid" => $job->price_paid == 0 ? False : True,
            ];
        }
        if ($role != 3) {
            $activity_logs = ActivityReport::where("job_id",$job->id)->get();
            foreach ($activity_logs as $activity_log) {
                $activity_logs_data[] = [
                    "message" => $activity_log->message,
                    "timestamp" => $activity_log->timestamp,
                    "image" => $s3SiteName . $activity_log->image,
                ];
            }
            $content_data += [
              "activity_logs" => $activity_logs_data
            ];
            $incident_reports = IncidentReport::where("job_id",$job->id)->get();
            foreach ($incident_reports as $incident_report) {
                $incident_report_data[] = [
                    "name" => $incident_report->name,
                    "message" => $incident_report->message,
                    "image" => $s3SiteName . $incident_report->image,
                ];
            }
            $content_data += [
              "incident_report" => $incident_report_data
            ];
        }

        return $content_data;
    }

    public static function jobFireLicense($fire_guard_license): array
    {
        return [
            "license_id" => $fire_guard_license->fire_guard_license_id,
            "license_name" => $fire_guard_license->fire_license->name,
        ];
    }

    public static function jobAcceptedDetails($job_detail): array
    {
        return [
            "security_guard_id" => $job_detail->guard_id,
            "security_guard_name" => $job_detail->users->first_name . " " . $job_detail->users->last_name,
            "clock_in_request" => $job_detail->clock_in_request == 0 ? FALSE : TRUE,
            "clock_in_request_accepted" => $job_detail->clock_in_request_accepted == 0 ? FALSE : TRUE,
            "clock_out_request" => $job_detail->clock_out_request == 0 ? FALSE : TRUE,
            "clock_out_request_accepted" => $job_detail->clock_out_request_accepted == 0 ? FALSE : TRUE,
        ];
    }

    public static function viewJobs($jobs, $customer_profile, $status, $job_details): array
    {
        $s3SiteName = Config::get('constants.s3_bucket');
        $job_status = null;
        $content_data = [
            "job_id" => $jobs->id,
            "job_type" => $jobs->job_type->name,
            "job_event_name" => $jobs->event_name,
            "job_state" => $jobs->state->name,
            "job_start_date" => $jobs->event_start,
            "job_posted_by_name" => $customer_profile->user->first_name,
            "job_posted_by_image" => $s3SiteName . $customer_profile->profile_image,
        ];
        if ($status == 0) {
            $content_data += [
                "job_description" => $jobs->job_description,
                "job_price" => $jobs->price,
                "job_max_price" => $jobs->max_price,
                "job_status_id" => $jobs->job_status,
                "job_status_name" => ConfigList::jobType($jobs->job_status),
            ];
        }
        if ($status == 1) {
            if ($job_details->clock_in_request == 1 && $job_details->clock_in_request_accepted == 1) {
                $content_data += [
                    "job_status_id" => 4,
                    "job_status_name" => ConfigList::jobType(4),
                ];
            } else {
                $content_data += [
                    "job_status_id" => 1,
                    "job_status_name" => ConfigList::jobType(1),
                ];
            }

        }
        if ($status == 2) {
            $content_data += [
                "job_status_id" => 2,
                "job_status_name" => ConfigList::jobType(2),
            ];
        }
        return $content_data;
    }
}
