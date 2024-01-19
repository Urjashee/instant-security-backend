<?php

namespace App\Common\FunctionHelpers;


use App\Common\ConfigList;
use App\Common\ResponseFormatter;
use App\Common\StringTemplate;
use App\Constants;
use App\Jobs\JobInformation;
use App\Models\ActivityReport;
use App\Models\CustomerProfile;
use App\Models\FireGuardLicense;
use App\Models\IncidentReport;
use App\Models\JobDetail;
use App\Models\SecurityJob;
use App\Models\StateLicense;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use PHPUnit\TextUI\XmlConfiguration\Constant;

class JobFunctions
{
    public static function authenticateUser($job_id, $user, $type)
    {
        if ($type == Constants::MOBILE_USER) {
            $job = JobDetail::where("guard_id", $user)
                ->where("job_id", $job_id)
                ->first();
            if ($job) {
                return (true);
            } else {
                return (false);
            }
        }
        if ($type == Constants::WEB_USER) {
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

    public static function checkUserStatus($user_id): bool
    {
        $user = User::where("id",$user_id)->first();
        if($user->status == 1) {
            return (true);
        } else {
            return (false);
        }
    }
    public static function nextJobStatus($user_id, $job_id): bool
    {
        $job = SecurityJob::where("id",$job_id)->first();
        if($job) {
            $time1 = Carbon::createFromTimestamp($job->event_start);
            $jobDetails = JobDetail::where("guard_id",$user_id)->get();
            foreach ($jobDetails as $jobDetail) {
                $time2 = Carbon::createFromTimestamp($jobDetail->jobs->event_start);
                if (($time2->diffInMinutes($time1) <= 240) && $jobDetail->jobs->status != Constants::CANCELLED) {
                    return (false);
                }
            }
        } else {
            return (true);
        }
        return (true);
    }
    public static function licenceExpiry($user_id, $job_id): bool
    {
        $job = SecurityJob::where("id",$job_id)->first();
        if($job) {
            $personalLicense = UserProfile::where("user_id", $user_id)->first();
            if ($personalLicense) {
//                return ([$personalLicense->govt_id_expiry_date , Carbon::createFromTimestamp($job->event_start)->format('Y-m-d')]);
                if (($personalLicense->govt_id_expiry_date < Carbon::createFromTimestamp($job->event_start)->format('Y-m-d')) ||
                    ($personalLicense->osha_license_expiry_date < Carbon::createFromTimestamp($job->event_start)->format('Y-m-d'))) {
                    return (false);
                }
            }
            $stateLicense = StateLicense::where("user_id", $user_id)->first();
            if ($stateLicense) {
                if (($stateLicense->security_guard_license_expiry < Carbon::createFromTimestamp($job->event_start)->format('Y-m-d')) ||
                    ($stateLicense->cpr_certificate_expiry < Carbon::createFromTimestamp($job->event_start)->format('Y-m-d'))) {
                    return (false);
                }
            }
            $fireLicenses = FireGuardLicense::where("user_id", $user_id)
                ->where("state_id",$job->state_id)
                ->get();
            if ($fireLicenses) {
                foreach ($fireLicenses as $fireLicense) {
                    if (($fireLicense->fire_guard_license_expiry < Carbon::createFromTimestamp($job->event_start)->format('Y-m-d'))) {
                        return (false);
                    }
                }
            }
            else {
                return (true);
            }
        }
        return true;
    }

    public static function jobDetails($job, $role, $status): array
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
        if ($status == 4) {
            $content_data += [
                "job_status_id" => 4,
                "job_status_name" => ConfigList::jobType(4),
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
        if ($role == 1) {
            $content_data += [
                "job_price_paid" => $job->price_paid == 0 ? False : True,
            ];
        }
        if ($role == 4) {
            $content_data += [
                "job_price_paid" => $job->price_paid == 0 ? False : True,
            ];
        }
        if ($role == 3) {
            $activity_logs = ActivityReport::where("job_id", $job->id)->get();
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
        }
        if ($role != 3) {
            if ($job->job_status == 2) {
                $activity_logs = ActivityReport::where("job_id", $job->id)->get();
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
            }
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
            "job_start_date" => Carbon::createFromTimestamp($jobs->event_start)->format('Y-m-d\TH:i:s.uP'),
            "job_posted_by_name" => $customer_profile->user->first_name,
            "job_posted_by_image" => $s3SiteName . $customer_profile->profile_image,
        ];
        if ($status == 0) {
            $content_data += [
                "job_description" => $jobs->job_description,
                "job_roles_and_responsibility" => $jobs->roles_and_responsibility,
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

    public static function extraTimeRequest($job_id): bool
    {
        $job = SecurityJob::where("id", $job_id)->first();
        if ($job->additional_hour_request == true && $job->additional_hours_accepted == 0) {
            return (true);
        } else {
            return (false);
        }
    }
    public static function clockOutRequests($request, $job_details): bool
    {
        $job_details->clock_out_request = Constants::ACCEPTED;
        $job_details->clock_out_time = $request->input("clock_out_time");
        $job_details->clock_out_latitude = $request->input("latitude");
        $job_details->clock_out_longitude = $request->input("longitude");
        $job_details->update();

        JobInformation::dispatch(
            $job_details->users->email,
            StringTemplate::typeMessage(Constants::MSG_CLOCK_OUT, $job_details->jobs->event_name, null, $job_details->job_id),
        );
        try {
            TwillioHelper::sendSms($job_details->users->phone_no,
                StringTemplate::typeMessage(Constants::MSG_CLOCK_OUT, $job_details->jobs->event_name, null, $job_details->job_id));
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public static function checkAdditionalTime($job, $job_detail) {
        if ($job->additional_hours_accepted) {
            $hours = $job->total_hours + $job->additional_hours;
            $job->event_add = $job->event_add + $job->additional_hours;
            $job->additional_hours_accepted = Constants::ACCEPTED;
        } else {
            $hours = $job->total_hours;
        }
        $job->total_price = $hours * $job->price;
        $job->update();
    }

    public static function jobCompleted($job_id): bool
    {
        $job = SecurityJob::where("id", $job_id)
            ->where("job_status", Constants::COMPLETED)
            ->first();
        if ($job) {
            return (true);
        } else {
            return (false);
        }
    }

    public static function pagination($request): array
    {
        $take = Constants::TAKE;
        $page = $request->query("page");
        $page ? $page = $request->query("page") : $page = Constants::DEFAULT_PAGE;
        $skip = ($page - 1) * $take;

        return [$skip, $take, $page];
    }

    public static function pageDetails($page, $total): array
    {
        $take = Constants::TAKE;
        return [
            "total" => $total,
            "page" => $page,
            "last_page" => ceil($total / $take)
        ];
    }

}
