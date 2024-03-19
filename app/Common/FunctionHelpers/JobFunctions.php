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
        if ($type == Constants::ADMIN_USER) {
            return true;
        }
    }

    public static function checkUserStatus($user_id): bool
    {
        $user = User::where("id", $user_id)->first();
        if ($user->status == 1) {
            return (true);
        } else {
            return (false);
        }
    }

    public static function nextJobStatus($user_id, $job_id): bool
    {
        $job = SecurityJob::where("id", $job_id)->first();
        if ($job) {
            $time1 = Carbon::createFromTimestamp($job->event_start);
            $jobDetails = JobDetail::where("guard_id", $user_id)->get();
            foreach ($jobDetails as $jobDetail) {
                $time2 = Carbon::createFromTimestamp($jobDetail->jobs->event_start);
                $time3 = Carbon::createFromTimestamp($jobDetail->jobs->event_end);
                if ((($time2->diffInMinutes($time1) <= 240) || ($time3 > $time1)) && ($jobDetail->jobs->job_status != Constants::CANCELLED || $jobDetail->jobs->job_status != Constants::COMPLETED)) {
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
        $job = SecurityJob::where("id", $job_id)->first();
        if ($job) {
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
                ->where("state_id", $job->state_id)
                ->get();
            if ($fireLicenses) {
                foreach ($fireLicenses as $fireLicense) {
                    if (($fireLicense->fire_guard_license_expiry < Carbon::createFromTimestamp($job->event_start)->format('Y-m-d'))) {
                        return (false);
                    }
                }
            } else {
                return (true);
            }
        }
        return true;
    }

    public static function bankingDetails($user_id): bool
    {
        $userBanking = UserProfile::where("user_id", $user_id)
            ->first();
        if ($userBanking) {
            if ($userBanking->account_number == null || $userBanking->routing == null)
                return false;
            else {
                return true;
            }
        }
        return false;
    }

    public static function alreadyApplied($user_id, $job_id): bool
    {
        $userApplied = JobAppliedGuard::where("guard_id", $user_id)
            ->where('job_id', $job_id)
            ->first();
        if ($userApplied) {
            return false;
        } else {
            return true;
        }
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
            "job_chat_id" => $job->chat_sid == null ? "" : $job->chat_sid,
        ];

        if ($status == 0) {
            $assigned_job_true = false;
            $assigned_job_all = false;
            if ($role == 2 && ($job->job_status == Constants::OPEN ||
                    $job->job_status == Constants::UPCOMING ||
                    $job->job_status == Constants::REJECTED_JOB ||
                    $job->job_status == Constants::PENDING_ASSIGNMENT ||
                    $job->job_status == Constants::PENDING)) {
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
                        $guards_data[] = [
                            "guard_id" => $applied_job->guard_id,
                            "guard_name" => $applied_job->user->first_name . " " . $applied_job->user->last_name
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
        if ($role == 2 || $role == 1) {
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

    public static function viewJobs($jobs, $customer_profile, $status, $job_details, $user_id): array
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
            $applied_job = JobAppliedGuard::where('guard_id', $user_id)
                ->where('job_id', $jobs->id)
                ->first();
            if ($applied_job) {
                $content_data += [
                    "job_description" => $jobs->job_description,
                    "job_roles_and_responsibility" => $jobs->roles_and_responsibility,
                    "job_price" => $jobs->price,
                    "job_max_price" => $jobs->max_price,
                    "job_status_id" => 8,
                    "job_status_name" => ConfigList::jobType(8),
                ];
            } else {
                $content_data += [
                    "job_description" => $jobs->job_description,
                    "job_roles_and_responsibility" => $jobs->roles_and_responsibility,
                    "job_price" => $jobs->price,
                    "job_max_price" => $jobs->max_price,
                    "job_status_id" => $jobs->job_status,
                    "job_status_name" => ConfigList::jobType($jobs->job_status),
                ];
            }
        }
        if ($status == 1) {
            if ($jobs->job_status == 1) {
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
            if ($jobs->job_status == 8) {
                $content_data += [
                    "job_status_id" => 8,
                    "job_status_name" => ConfigList::jobType(8),
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

    public static function clockOutRequests($request, $job_details, $job): bool
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
        (new NotificationController())->addNotifications($job->id, $request->input(Constants::CURRENT_USER_ID_KEY),
            $job->user_id, 4, StringTemplate::typeMessage(Constants::MSG_CLOCK_OUT, $job_details->jobs->event_name, null, $job_details->job_id));
        try {
            TwillioHelper::sendSms($job_details->users->phone_no,
                StringTemplate::typeMessage(Constants::MSG_CLOCK_OUT, $job_details->jobs->event_name, null, $job_details->job_id));
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public static function checkAdditionalTime($job, $job_detail)
    {
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
