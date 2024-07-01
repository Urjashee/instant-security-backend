<?php

namespace App\Common\FunctionHelpers;


use App\Common\ConfigList;
use App\Common\ResponseFormatter;
use App\Common\StringTemplate;
use App\Constants;
use App\Http\Controllers\NotificationController;
use App\Jobs\JobInformation;
use App\Models\ActivityReport;
use App\Models\CustomerProfile;
use App\Models\FireGuardLicense;
use App\Models\IncidentReport;
use App\Models\JobAppliedGuard;
use App\Models\JobDetail;
use App\Models\JobReview;
use App\Models\SecurityJob;
use App\Models\StateLicense;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use PHPUnit\TextUI\XmlConfiguration\Constant;

class JobFunctions1
{
    public static function jobDetails($job, $role, $status, $job_details): array
    {
        $s3SiteName = Config::get('constants.s3_bucket');
        $customer_profile = CustomerProfile::where("user_id", $job->user_id)->first();
        $activity_logs_data = array();
        $incident_report_data = array();
        $content_data = [
            "job_id" => $job->id,
            "job_event_name" => $job->event_name,
            "job_description" => $job->job_description,
            "job_roles_and_responsibility" => $job->roles_and_responsibility,
            "job_type_id" => $job->job_type_id,
            "job_type" => $job->job_type->name,
            "job_osha_license_id" => $job->osha_license_id == null ? "" : $job->osha_license_id,
            "job_osha_license" => $job->osha_license_id == null ? "" : ConfigList::oshaType($job->osha_license_id),
            "job_state_id" => $job->state_id,
            "job_state" => $job->state->name,
            "job_start_date" => Carbon::createFromTimestamp($job->event_start)->format('Y-m-d\TH:i:s.uP'),
            "job_price" => $job->price,
            "job_max_price" => $job->max_price,
            "job_start_time" => Carbon::createFromTimestamp($job->event_start)->format('Y-m-d\TH:i:s.uP'),
            "job_end_time" => Carbon::createFromTimestamp($job->event_end)->format('Y-m-d\TH:i:s.uP'),
            "job_address" => $job->street1 . ", " . $job->street2 . ", " . $job->city . ", " . $job->state->name . ", " . $job->zipcode,
            "additional_hour_request" => !($job->additional_hour_request == 0),
            "additional_hours" => $job->additional_hours == null ? 0 : $job->additional_hours,
            "additional_hours_accepted" => !($job->additional_hours_accepted == 0),
            "job_posted_by_id" => $job->user_id,
            "job_posted_by_name" => $job->users->first_name . " " . $job->users->last_name,
            "job_posted_by_image" => $s3SiteName . $customer_profile->profile_image,
            "job_chat_id" => $job->chat_sid == null ? "" : $job->chat_sid,
        ];

        if ($status == 0) {
            $assigned_job_true = false;
            $assigned_job_all = false;
            $job_detail = JobDetail::where("job_id", $job->id)->first();
            if ($role == 2 && ($job->job_status == Constants::OPEN ||
                    $job->job_status == Constants::UPCOMING ||
                    $job->job_status == Constants::REJECTED_JOB ||
                    $job->job_status == Constants::PENDING_ASSIGNMENT)) {
                if ($job->job_status == Constants::OPEN ||
                    $job->job_status == Constants::PENDING_ASSIGNMENT) {
                    $content_data += [
                        "job_status_id" => Constants::UPCOMING,
                        "job_status_name" => ConfigList::jobType(Constants::UPCOMING),
                    ];
                } else {
                    $content_data += [
                        "job_status_id" => $job->job_status,
                        "job_status_name" => ConfigList::jobType($job->job_status),
                    ];
                }
            } else if ($role == 1 && ($job->job_status == 0 || $job->job_status == 1 || $job->job_status == 8)) {
                $applied_jobs = JobAppliedGuard::where('job_id', $job->id)
                    ->where('assigned', Constants::INACTIVE)
                    ->get();
                $applied_jobs_active = JobAppliedGuard::where('job_id', $job->id)
                    ->where('assigned', Constants::ACTIVE)
                    ->first();
                if ($applied_jobs_active) {
                    $assigned_job_true = true;
                }
                if ($applied_jobs) {
                    $assigned_job_all = true;
                    $guards_data = array();
                    foreach ($applied_jobs as $applied_job) {
                        $user_profile = UserProfile::where("user_id",$applied_job->guard_id)->first();
                        $guards_data[] = [
                            "guard_id" => $applied_job->guard_id,
                            "guard_name" => $applied_job->user->first_name . " " . $applied_job->user->last_name,
                            "guard_image" => $user_profile->profile_image == null ? "" : $s3SiteName . $user_profile->profile_image
                        ];
                    }
                    $content_data += [
                        "applied_guards" => $guards_data,
                    ];
                }
                if ($assigned_job_true && $assigned_job_all) {
                    $content_data += [
                        "job_status_id" => 9,
                        "job_status_name" => ConfigList::jobType(9),
                    ];
                }
                if (!$assigned_job_true && $assigned_job_all) {
                    $content_data += [
                        "job_status_id" => 8,
                        "job_status_name" => ConfigList::jobType(8),
                    ];
                }
                if (!$assigned_job_true && !$assigned_job_all) {
                    $content_data += [
                        "job_status_id" => $job->job_status,
                        "job_status_name" => ConfigList::jobType($job->job_status),
                    ];
                }

            }
        }
        if ($status == 1) {
            $job_detail = JobDetail::where("job_id", $job->id)->first();
            if ($role == 3) {
                if ($job->security_jobs->clock_in_request == 1 && $job->security_jobs->clock_in_request_accepted == 1) {
                    $content_data += [
                        "job_status_id" => Constants::ONGOING,
                        "job_status_name" => ConfigList::jobType(Constants::ONGOING),
                    ];
                } else {
                    $content_data += [
                        "job_status_id" => Constants::UPCOMING,
                        "job_status_name" => ConfigList::jobType(1),
                    ];
                }
            } else {
                if ($job_detail->clock_in_request == 1 && $job_detail->clock_in_request_accepted == 0) {
                    $content_data += [
                        "job_status_id" => Constants::UPCOMING,
                        "job_status_name" => "Clock-in request",
                    ];
                } else if ($job_detail->clock_in_request == 1 && $job_detail->clock_in_request_accepted == 1 && $job_detail->clock_out_request == 0) {
                    $content_data += [
                        "job_status_id" => Constants::ONGOING,
                        "job_status_name" => ConfigList::jobType(Constants::ONGOING),
                    ];
                } else if ($job_detail->clock_out_request == 1 && $job_detail->clock_out_request_accepted == 0) {
                    $content_data += [
                        "job_status_id" => Constants::ONGOING,
                        "job_status_name" => "Clock-out request",
                    ];
                } else {
                    $content_data += [
                        "job_status_id" => Constants::OPEN,
                        "job_status_name" => ConfigList::jobType(Constants::OPEN),
                    ];
                }
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
        if ($status == 4) {
            $content_data += [
                "job_status_id" => Constants::ONGOING,
                "job_status_name" => ConfigList::jobType(Constants::ONGOING),
            ];
        }
        if ($status == 6) {
            if ($job->job_status == Constants::PENDING)
                $content_data += [
                    "job_status_id" => Constants::PENDING,
                    "job_status_name" => ConfigList::jobType(Constants::PENDING),
                ];
            if ($job->job_status == Constants::REJECTED_JOB)
                $content_data += [
                    "job_status_id" => Constants::REJECTED_JOB,
                    "job_status_name" => ConfigList::jobType(Constants::REJECTED_JOB),
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
//        if ($role != 2) {
//            $content_data += [
//                "job_customer_id" => $job->user_id,
//                "job_customer_name" => $job->users->first_name . " " . $job->users->last_name,
//            ];
//        }
        if ($role == 2 || $role == 1 || $role == 3) {
            $job_review = JobReview::where("job_id", $job->id)->first();
            if ($job_review) {
                $content_data += [
                    "job_review" => true,
                    "job_review_rating" => $job_review->rating,
                    "job_review_message" => $job_review->message,
                ];
            } else {
                $content_data += [
                    "job_review" => false
                ];
            }
        }
        if ($role == 1) {
            $content_data += [
                "job_price_paid" => $job->price_paid == 0 ? False : True,
            ];
        }
        if ($role == 2) {
            $content_data += [
                "osha_license_id" => $job->osha_license_id == null ? "" : $job->osha_license_id ,
                "osha_license_name" => $job->osha_license_id == null ? "" : ConfigList::oshaType($job->osha_license_id),
            ];
            if ($job_details != null) {
                $content_data += [
                    "clock_in_request" => $job_details->clock_in_request == 0 ? FALSE : TRUE,
                    "clock_in_request_accepted" => $job_details->clock_in_request_accepted == 0 ? FALSE : TRUE,
                    "clock_in_time" => $job_details->clock_in_time == null ? "" : $job_details->clock_in_time,
                    "clock_out_request" => $job_details->clock_out_request == 0 ? FALSE : TRUE,
                    "clock_out_request_accepted" => $job_details->clock_out_request_accepted == 0 ? FALSE : TRUE,
                    "clock_out_time" => $job_details->clock_out_time == null ? "" : $job_details->clock_out_time,
                ];
            }
        }
        if ($role == 3) {
            $content_data += [
                "job_price_paid" => $job->price_paid == 0 ? False : True,
            ];
        }
        if ($role == 3) {
            $activity_logs = ActivityReport::where("job_id", $job->id)->get();
            foreach ($activity_logs as $activity_log) {
                $activity_logs_data[] = [
                    "message" => $activity_log->message,
                    "timestamp" => Carbon::createFromTimestamp($activity_log->timestamp)->format('Y-m-d\TH:i:s.uP'),
                    "image" => $activity_log->image == null ? "" : $s3SiteName . $activity_log->image,
                ];
            }
            $content_data += [
                "activity_logs" => $activity_logs_data
            ];
        }
        if ($role != 3) {
            if ($job->job_status == 2) {
                $activity_logs = ActivityReport::where("job_id", $job->id)->get();
                foreach ($activity_logs as $activity_log) {
                    $activity_logs_data[] = [
                        "message" => $activity_log->message,
                        "timestamp" => Carbon::createFromTimestamp($activity_log->timestamp)->format('Y-m-d\TH:i:s.uP'),
                        "image" => $activity_log->image == null ? "" : $s3SiteName . $activity_log->image,
                    ];
                }
                $content_data += [
                    "activity_logs" => $activity_logs_data
                ];
            }
            $incident_reports = IncidentReport::where("job_id", $job->id)->get();
            foreach ($incident_reports as $incident_report) {
                $incident_report_data[] = [
                    "name" => $incident_report->name,
                    "message" => $incident_report->message,
                    "image" => $incident_report->image == null ? "" : $s3SiteName . $incident_report->image,
                ];
            }
            $content_data += [
                "incident_report" => $incident_report_data
            ];
        }

        return $content_data;
    }

}
