<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\JobFunctions;
use App\Common\FunctionHelpers\Pagination;
use App\Common\FunctionHelpers\ProfileFunctions;
use App\Common\FunctionHelpers\StripeHelper;
use App\Common\FunctionHelpers\TwillioHelper;
use App\Common\ResponseFormatter;
use App\Common\StringTemplate;
use App\Constants;
use App\Jobs\JobInformation;
use App\Mail\JobInfo;
use App\Models\ActivityReport;
use App\Models\CustomerProfile;
use App\Models\FireGuardLicense;
use App\Models\IncidentReport;
use App\Models\JobAppliedGuard;
use App\Models\JobDetail;
use App\Models\JobFireLicense;
use App\Models\JobType;
use App\Models\RejectedJobUser;
use App\Models\JobReview;
use App\Models\SecurityJob;
use App\Models\State;
use App\Models\StateLicense;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
//            "street2" => "required",
            "city" => "required",
            "zipcode" => "required",
            "event_start" => "required",
            "event_end" => "required",
            "osha_license_id" => "required",
            "roles_and_responsibility" => "required"
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        if (!State::where("id", $request->input("state_id"))
            ->where("active", 1)->first())
            return ResponseFormatter::successResponse("Not a valid state_id");

        if (!JobType::where("id", $request->input("job_type_id"))->first())
            return ResponseFormatter::successResponse("Not a valid job_type_id");

        if ($request->input("event_start") <= time())
            return ResponseFormatter::errorResponse("Job start time cannot be less than current time");

        $customer_profile = CustomerProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($customer_profile) {
            try {
                $cardList = StripeHelper::getPaymentMethodList($customer_profile->customer_id);
            } catch (\Exception $e) {
                return ResponseFormatter::errorResponse($e->getMessage());
            }
            if (sizeof($cardList) <= 0) {
                return ResponseFormatter::errorResponse("Customer doesn't have a payment method");
            } else {
                $jobType = JobType::where("id", $request->input("job_type_id"))->first();

                if ($jobType) {
                    try {
                        $conversation = TwillioHelper::createConversation($request->input("event_name"));
                    } catch (\Exception $e) {
                        return ResponseFormatter::errorResponse($e->getMessage());
                    }
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
                    $time1 = Carbon::createFromTimestamp($request->input("event_end"));
                    $time2 = Carbon::createFromTimestamp($request->input("event_start"));
                    $newJobs->total_hours = $time2->diffInMinutes($time1) / 60;
                    $newJobs->osha_license_id = $request->input("osha_license_id");
                    $newJobs->job_description = $request->input("job_description");
                    $newJobs->roles_and_responsibility = $request->input("roles_and_responsibility");
                    $newJobs->price = $jobType->hourly_rate;
                    $difference = $request->input("event_end") - $request->input("event_start");
                    $total_price = round(($difference / 3600) * $jobType->hourly_rate);
                    $newJobs->max_price = $total_price;
                    $newJobs->total_price = $total_price;
                    $newJobs->price_paid = 0;
                    $newJobs->job_status = Constants::PENDING;
                    $newJobs->chat_sid = $conversation->sid;
                    $newJobs->chat_service_sid = $conversation->chatServiceSid;
                    try {
                        $createPrice = StripeHelper::createPrice($total_price, $jobType->name);
                    } catch (\Exception $e) {
                        return ResponseFormatter::errorResponse($e->getMessage());
                    }
                    $newJobs->price_id = $createPrice->id;
                    try {
                        $invoiceItem = StripeHelper::createInvoiceItem($customer_profile->customer_id, $createPrice->id);
                    } catch (\Exception $e) {
                        return ResponseFormatter::errorResponse($e->getMessage());
                    }
                    $newJobs->invoice_item_id = $invoiceItem->id;
                    try {
                        $invoice = StripeHelper::createInvoices($customer_profile->customer_id);
                    } catch (\Exception $e) {
                        return ResponseFormatter::errorResponse($e->getMessage());
                    }
                    $newJobs->invoice_id = $invoice->id;

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
                } else {
                    return ResponseFormatter::errorResponse("No such Job type");
                }
                return ResponseFormatter::successResponse("Job successfully added!");
            }
        }
    }

    public function getJobs(Request $request): \Illuminate\Http\JsonResponse
    {
        $contentData = array();
        $status = $request->query("status");

        if ($status == 0) {
            $jobs = SecurityJob::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
                ->where(function ($query) use ($status) {
                    $query->where("job_status", $status)
                        ->orWhere("job_status", Constants::PENDING)
                        ->orWhere("job_status", Constants::REJECTED_JOB)
                        ->orWhere("job_status", Constants::UPCOMING)
                        ->orWhere("job_status", Constants::PENDING_ASSIGNMENT);
                })
                ->orderBy("security_jobs.created_at", "DESC")
                ->get();
        }
        if ($status == 1) {
            $jobs = SecurityJob::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
                ->where("job_status", Constants::UPCOMING)
                ->orderBy("security_jobs.created_at", "DESC")
                ->get();
        } else if ($status == Constants::COMPLETED || $status == Constants::ONGOING || $status == Constants::CANCELLED) {
            $jobs = SecurityJob::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
                ->where("job_status", $status)
                ->orderBy("security_jobs.created_at", "DESC")
                ->get();
        }
        if ($jobs) {
            foreach ($jobs as $job) {
                $job_data = JobFunctions::jobDetailsCustomer($job, $request->input(Constants::CURRENT_ROLE_ID_KEY), $status, null);
                $contentData[] = $job_data;
                $contentData = array_filter($contentData, function ($item) {
                    return !empty($item);
                });
                $contentData = array_values($contentData);
            }
            return ResponseFormatter::successResponse("Jobs", $contentData);
        } else {
            return ResponseFormatter::errorResponse("Jobs list is empty");
        }
    }

    public function getAllJobs(Request $request): \Illuminate\Http\JsonResponse
    {
        $contentData = array();
        $status = $request->query("status");

        if ($status == 6) {
            $jobs = SecurityJob::whereIn("job_status", [Constants::PENDING, Constants::REJECTED_JOB])
                ->orderBy("security_jobs.created_at", "DESC")
                ->get();
        } else if ($status == 0) {
//TODO
            $jobs = SecurityJob::whereIn("job_status", [Constants::OPEN, Constants::UPCOMING,
                Constants::PENDING_ASSIGNMENT])
                ->orderBy("security_jobs.created_at", "DESC")
                ->get();

        } else if ($status == 1) {
            $jobs = SecurityJob::where("job_status", [Constants::UPCOMING,
                Constants::ASSIGNED])
                ->orderBy("security_jobs.created_at", "DESC")
                ->get();
        } else if ($status == 2) {
            $jobs = SecurityJob::where("job_status", Constants::COMPLETED)
                ->orderBy("security_jobs.created_at", "DESC")
                ->get();
        } else if ($status == 3) {
            $jobs = SecurityJob::where("job_status", Constants::CANCELLED)
                ->orderBy("security_jobs.created_at", "DESC")
                ->get();
        }
        if ($jobs) {
            foreach ($jobs as $job) {
                $job_data = JobFunctions::jobDetailsAdmin($job, $request->input(Constants::CURRENT_ROLE_ID_KEY), $status, null);
                $contentData[] = $job_data;
                $contentData = array_filter($contentData, function ($item) {
                    return !empty($item);
                });
                $contentData = array_values($contentData);
            }
            return ResponseFormatter::successResponse("Jobs", $contentData);
        } else {
            return ResponseFormatter::errorResponse("Jobs list is empty");
        }
    }

    public function getJobsById(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $contentData = array();
        $jobDetailsData = array();
        $fireGuardLicenseData = array();

        if ($request->input(Constants::CURRENT_ROLE_ID_KEY) == Constants::WEB_USER) {
            $auth_user = JobFunctions::authenticateUser($id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::WEB_USER);
            if (!$auth_user)
                return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        }
//
//        if ($request->input(Constants::CURRENT_ROLE_ID_KEY) == Constants::MOBILE_USER) {
//            $auth_user = JobFunctions::authenticateUser($id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
//            if (!$auth_user)
//                return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
//        }

        $jobs = SecurityJob::where("id", $id)->first();
        $job_details = JobDetail::where("job_id", $id)->first();
        $fire_guard_licenses = JobFireLicense::where("job_id", $id)->get();

        if ($jobs) {
            if ($fire_guard_licenses) {
                foreach ($fire_guard_licenses as $fire_guard_license) {
                    $fire_guard_license_data = JobFunctions::jobFireLicense($fire_guard_license);
                    $fireGuardLicenseData[] = $fire_guard_license_data;
                }
            }
            if ($job_details) {
                $job_detail_data = JobFunctions::jobAcceptedDetails($job_details);
                $jobDetailsData = $job_detail_data;
            }
            $job_data = JobFunctions::jobDetails($jobs, $request->input(Constants::CURRENT_ROLE_ID_KEY), null, $job_details);
            $contentData = $job_data;
            if ($request->input(Constants::CURRENT_ROLE_ID_KEY) != Constants::MOBILE_USER) {
                $contentData += [
                    "job_fire_guard_license" => $fireGuardLicenseData,
                    "job_guard_details" => $jobDetailsData
                ];
            }
            return ResponseFormatter::successResponse("Jobs", $contentData);
        } else {
            return ResponseFormatter::errorResponse("Jobs list is empty");
        }
    }

    public function getOpenJobs(Request $request): \Illuminate\Http\JsonResponse
    {
        $content_data = array();
        $fire_licenses = array();
        $state_licenses = array();
        $job_lists = array();
        $job_lists_unique = array();

        $offset = $request->query("offset");
        $limit = $request->query("limit");

        $user = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        $fire_guard_licenses = FireGuardLicense::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->get();
        $user_state_licenses = StateLicense::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->get();

        foreach ($user_state_licenses as $user_state_license) {
            $state_licenses[] = $user_state_license->state_id;
        }
        foreach ($fire_guard_licenses as $fire_guard_license) {
            $fire_licenses[] = $fire_guard_license->fire_guard_license_type;
        }

        $jobsLicense = JobFireLicense::select("*")
            ->join("security_jobs", "security_jobs.id", "=", "job_fire_license.job_id")
            ->where("security_jobs.osha_license_id", $user->osha_license_type)
            ->where("security_jobs.job_status", Constants::OPEN)
            ->where("security_jobs.event_start", ">", strtotime(Carbon::now()))
            ->orderBy("security_jobs.created_at", "DESC")//            ->get()
        ;
        if ($offset && $limit) {
            $jobsLicense->offset($offset);
            $jobsLicense->limit($limit);
        } else {
            $jobsLicense->offset(0);
            $jobsLicense->limit(Constants::TAKE);
        }

        $jobsLicenses = $jobsLicense->get();

        foreach ($jobsLicenses as $jobsLicense) {
            if (in_array($jobsLicense->fire_guard_license_id, $fire_licenses)) {
                $job_lists[] = $jobsLicense->job_id;
                $cancelled_job_users = RejectedJobUser::where("job_id", $jobsLicense->job_id)
                    ->where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
                    ->first();
                if (!$cancelled_job_users) {
                    $job_lists[] = $jobsLicense->job_id;
                }
            }
        }

        if ($job_lists != null) {
            $job_lists_unique = array_unique($job_lists);
            foreach ($job_lists_unique as $job_list) {
                $jobs = SecurityJob::where("id", $job_list)->first();
                if ($jobs && (in_array($jobs->state_id, $state_licenses))) {
                    $customer_profile = CustomerProfile::where("user_id", $jobs->user_id)->first();
                    $view_jobs_data = JobFunctions::viewJobs($jobs, $customer_profile, Constants::OPEN, null, $request->input(Constants::CURRENT_USER_ID_KEY));
                    $content_data[] = $view_jobs_data;
                }
            }
        }
        return ResponseFormatter::successResponse("Job list", $content_data);
    }

    public function selectedJobs(Request $request): \Illuminate\Http\JsonResponse
    {
        $content_data = array();
        $status = Constants::UPCOMING;
        if ($request->query("status")) {
            $status = $request->query("status");
        }
        if ($status == Constants::UPCOMING) {
            $jobs = DB::table("security_jobs")
                ->leftJoin("job_details", "security_jobs.id", "=", "job_details.job_id")
                ->leftJoin("job_applied_guards", "security_jobs.id", "=", "job_applied_guards.job_id")
                ->select("security_jobs.*", "security_jobs.id as security_id", "job_details.*", "job_applied_guards.*")
                ->where("security_jobs.job_status", Constants::UPCOMING)
                ->where("security_jobs.job_status", Constants::PENDING_ASSIGNMENT)
                ->where("job_details.guard_id", $request->input(Constants::CURRENT_USER_ID_KEY))
                ->orWhere("job_applied_guards.guard_id", $request->input(Constants::CURRENT_USER_ID_KEY))
                ->orderBy("security_jobs.created_at", "DESC")
                ->get();
            if ($jobs) {
                foreach ($jobs as $job) {
                    $customer_profile = CustomerProfile::where("user_id", $job->user_id)->first();
                    $view_jobs_data = JobFunctions::viewSelectedJobs($customer_profile, $status, $job, $request->input(Constants::CURRENT_USER_ID_KEY));
//                    $content_data[] = $job->security_id;
                    $content_data[] = $view_jobs_data;
                }
                $content_data = array_filter($content_data);
                $content_data = array_values($content_data);
                return ResponseFormatter::successResponse("Jobs", $content_data);
            } else {
                return ResponseFormatter::errorResponse("No job records");
            }

        }
        if ($status == Constants::COMPLETED) {
            $jobs = JobDetail::where("guard_id", $request->input(Constants::CURRENT_USER_ID_KEY))
                ->orderBy("job_details.created_at", "DESC")
                ->get();
            if ($jobs) {
                foreach ($jobs as $job) {
                    $security_jobs = SecurityJob::where("id", $job->job_id)->where("job_status", $status)->first();
                    if ($security_jobs) {
                        $customer_profile = CustomerProfile::where("user_id", $security_jobs->user_id)->first();
                        $view_jobs_data = JobFunctions::viewJobs($security_jobs, $customer_profile, $status, $job, null);
                        $content_data[] = $view_jobs_data;
                    }
                }
                return ResponseFormatter::successResponse("Jobs", $content_data);
            } else {
                return ResponseFormatter::errorResponse("No job records");
            }
        } else {
            return ResponseFormatter::errorResponse("No job records");
        }
    }

    public function updateJobStatus(Request $request, $job_id, $status): \Illuminate\Http\JsonResponse
    {
        $auth_user = ProfileFunctions::checkEditStatus($request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if ($auth_user)
            return ResponseFormatter::forbiddenResponse("Forbidden action!");

        $user = User::where("id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($status == Constants::ACCEPTED) {
            $auth_user = JobFunctions::checkUserStatus($request->input(Constants::CURRENT_USER_ID_KEY));
//            $next_job_status = JobFunctions::nextJobStatus($request->input(Constants::CURRENT_USER_ID_KEY), $job_id);
            $license_expiry = JobFunctions::licenceExpiry($request->input(Constants::CURRENT_USER_ID_KEY), $job_id);
            $already_applied = JobFunctions::alreadyApplied($request->input(Constants::CURRENT_USER_ID_KEY), $job_id);
            $banking_details = JobFunctions::bankingDetails($request->input(Constants::CURRENT_USER_ID_KEY));
            if (!$auth_user) {
                return ResponseFormatter::unauthorizedResponse("User status is inactive");
            }
//            if (!$next_job_status) {
//                return ResponseFormatter::errorResponse(StringTemplate::response(1));
//            }
            if (!$license_expiry) {
                return ResponseFormatter::errorResponse(StringTemplate::response(2));
            }
            if (!$banking_details) {
                return ResponseFormatter::errorResponse(StringTemplate::response(3));
            }
            if (!$already_applied) {
                return ResponseFormatter::errorResponse(StringTemplate::response(4));
            } else {
                $assignJob = new JobAppliedGuard();
                $assignJob->job_id = $job_id;
                $assignJob->guard_id = $user->id;
                $assignJob->save();
                $security_job = SecurityJob::where("id", $job_id)
                    ->where("job_status", Constants::OPEN)
                    ->first();
                if ($security_job) {
                    $security_job->job_status = Constants::PENDING_ASSIGNMENT;
                    $security_job->update();
                }
                return ResponseFormatter::successResponse("Request sent");
            }
        } else {
            return ResponseFormatter::errorResponse("Not a valid response");
        }
    }

    public function assignJob(Request $request, $job_id, $user_id): \Illuminate\Http\JsonResponse
    {
        $next_job_status = JobFunctions::nextJobStatus($user_id, $job_id);
        if (!$next_job_status) {
            return ResponseFormatter::errorResponse(StringTemplate::response(5));
        }
        $check_applied_user = JobAppliedGuard::where('job_id', $job_id)
            ->where("guard_id", $user_id)
            ->first();
        if (!$check_applied_user) {
            return ResponseFormatter::errorResponse(StringTemplate::response(6));
        }
        $user = User::where("id", $user_id)->first();
        $job = SecurityJob::where("id", $job_id)
            ->where("job_status", Constants::PENDING_ASSIGNMENT)
            ->first();

        if ($job) {
            if ($job->participant_id == null) {
                try {
                    $participant = TwillioHelper::addChatParticipantToConversation($job->users->friendly_name, $job->chat_sid);
                } catch (\Exception $e) {
                    return ResponseFormatter::errorResponse($e->getMessage());
                }
                $job->participant_id = $participant;
            }

            $job_details = new JobDetail();
            $job_details->job_id = $job->id;
            $job_details->guard_id = $user_id;
            try {
                $participant_user = TwillioHelper::addChatParticipantToConversation($user->friendly_name, $job->chat_sid);
            } catch (\Exception $e) {
                return ResponseFormatter::errorResponse($e->getMessage());
            }
            $job_details->participant_id = $participant_user;
            $job_details->chat_sid = $job->chat_sid;
            $job_details->save();

            $job_applied = JobAppliedGuard::where('job_id', $job->id)
                ->get();
            if ($job_applied) {
                foreach ($job_applied as $applied) {
                    if ($applied->guard_id == $user_id) {
                        JobInformation::dispatch(
                            $applied->user->email,
                            StringTemplate::typeMessage(Constants::MSG_JOB_REQUEST_ACCEPTED, $job->event_name, null, $job->id),
                        );
                        $applied->assigned = Constants::ACCEPTED;
                        $applied->update();
                    } else {
                        JobInformation::dispatch(
                            $applied->user->email,
                            StringTemplate::typeMessage(Constants::MSG_JOB_REQUEST_REJECTED, $job->event_name, null, $job->id),
                        );
                    }
                }
            }

            $job->job_status = Constants::UPCOMING;
            $job->update();

            (new NotificationController())->addNotifications($job_id, $user_id, $job->user_id, 1, null);
            return ResponseFormatter::successResponse("Job has been updated");
        } else {
            return ResponseFormatter::errorResponse("Job has already been filled");
        }
    }

    public function cancelJobs(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $email_data = array($request->input(Constants::CURRENT_EMAIL_KEY), Config::get('constants.super_admin_email'));

        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        else {
            try {
                DB::beginTransaction();
                $job = SecurityJob::where("id", $job_id)
                    ->where("job_status", Constants::UPCOMING)
                    ->first();

                $job_details = JobDetail::where("job_id", $job_id)->first();
                $cancelled_job_user = new RejectedJobUser();
                try {
                    $delete_participant = TwillioHelper::deleteChatParticipantFromConversation($job->chat_sid, $job_details->participant_id);
                } catch (\Exception $e) {
                    return ResponseFormatter::errorResponse($e->getMessage() . $job->chat_sid);
                }
                if ($delete_participant) {
                    $job->job_status = Constants::OPEN;
                    $cancelled_job_user->user_id = $request->input(Constants::CURRENT_USER_ID_KEY);
                    $cancelled_job_user->job_id = $job_id;
                    $cancelled_job_user->save();
                    $job->update();
                    $job_details->delete();
                }
                DB::commit();
                foreach ($email_data as $email) {
                    JobInformation::dispatch(
                        $email,
                        StringTemplate::typeMessage(Constants::MSG_CANCELLED, $job->event_name, $request->input(Constants::CURRENT_FIRST_NAME_KEY), $job_id),
                    );
                }
                (new NotificationController())->addNotifications($job_id, $request->input(Constants::CURRENT_USER_ID_KEY),
                    $job->user_id, 2, StringTemplate::typeMessage(Constants::MSG_CANCELLED, $job->event_name, $request->input(Constants::CURRENT_FIRST_NAME_KEY), $job_id));
                return ResponseFormatter::successResponse("Job cancelled");
            } catch (\Exception $exception) {
                DB::rollback();
                return ResponseFormatter::errorResponse("Error in cancelling job");
            }
        }
    }

    public function cancelJobsCreated(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::WEB_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        else {
            $job = SecurityJob::where("id", $job_id)->first();
            $job_details = JobDetail::where("job_id", $job_id)->first();
            if ($job_details) {
                if ($job_details->clock_in_request_accepted == Constants::ACCEPTED) {
                    return ResponseFormatter::errorResponse("Can't cancel once the job has started");
                } else {
                    $job_details->chat_sid = null;
                    $job_details->participant_id = null;
                    $job_details->update();
                    try {
                        TwillioHelper::deleteConversationWithSid($job->chat_sid);
                    } catch (\Exception $e) {
                        return ResponseFormatter::errorResponse($e->getMessage());
                    }
                }
            }

            $job->job_status = Constants::CANCELLED;
            $job->chat_sid = null;
            $job->chat_service_sid = null;
            $job->participant_id = null;
            $job->update();

            try {
                StripeHelper::deleteInvoiceItem($job->invoice_item_id);
            } catch (\Exception $e) {
                return ResponseFormatter::errorResponse($e->getMessage());
            }
            if ($job_details) {
                try {
                    StripeHelper::voidInvoices($job->invoice_id);
                } catch (\Exception $e) {
                    return ResponseFormatter::errorResponse($e->getMessage());
                }
            }
            return ResponseFormatter::successResponse("Job cancelled successfully");

        }
    }

    public function clockInRequest(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        else {
            $job = SecurityJob::where("id", $job_id)->first();
            $time1 = $request->input("clock_in_time");
            $time2 = $job->event_start;
            if ($time1 > $job->event_end) {
                return ResponseFormatter::errorResponse("Clock-in time cannot be greater than event end time");
            }
            if ((($time2 - $time1) / 60 >= 30)) {
                return ResponseFormatter::errorResponse("Can't clock in before 30 minutes");
            } else {
                $job_details = JobDetail::where("job_id", $job_id)->first();
                if ($job_details->clock_in_request_accepted == Constants::ACCEPTED) {
                    return ResponseFormatter::errorResponse("Clock in request already accepted");
                } else {
                    $job_details->clock_in_request = Constants::ACCEPTED;
                    $job_details->clock_in_time = $request->input("clock_in_time");
                    $job_details->clock_in_latitude = $request->input("clock_in_latitude");
                    $job_details->clock_in_longitude = $request->input("clock_in_longitude");
                    $job_details->update();
                    JobInformation::dispatch(
                        $job->users->email,
                        StringTemplate::typeMessage(Constants::MSG_CLOCK_IN, $job->event_name, null, $job->id),
                    );
                    (new NotificationController())->addNotifications($job_id, $request->input(Constants::CURRENT_USER_ID_KEY),
                        $job->user_id, 3, StringTemplate::typeMessage(Constants::MSG_CLOCK_IN, $job->event_name, null, $job->id));
                    try {
                        TwillioHelper::sendSms($job->users->phone_no,
                            StringTemplate::typeMessage(Constants::MSG_CLOCK_IN, $job->event_name, null, $job->id));
                    } catch (\Exception $e) {
                        return ResponseFormatter::errorResponse("Clock-in request sent but message couldn't be delivered");
                    }
                    return ResponseFormatter::successResponse("Clock-in request sent");
                }
            }
        }
    }

    public function clockInResponse(Request $request, $job_id, $approval): \Illuminate\Http\JsonResponse
    {
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::WEB_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        else {
            $job_details = JobDetail::where("job_id", $job_id)->first();
            if ($approval == Constants::DENIED) {
                $job_details->clock_in_request = Constants::DENIED;
                $job_details->clock_in_time = null;
                $job_details->clock_in_latitude = null;
                $job_details->clock_in_longitude = null;
                $job_details->update();
                return ResponseFormatter::successResponse("Clock-in rejected");
            }
            if ($approval == Constants::ACCEPTED) {
                $job_details->clock_in_request_accepted = Constants::ACCEPTED;
                $job_details->update();
                return ResponseFormatter::successResponse("Clock-in accepted");
            }
        }
    }

    public function clockInResponseAdmin(Request $request, $job_id, $approval): \Illuminate\Http\JsonResponse
    {
        $auth_user = JobFunctions::authenticateUser($job_id, null, Constants::ADMIN_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        else {
            $job_details = JobDetail::where("job_id", $job_id)->first();
            if ($approval == Constants::DENIED) {
                $job_details->clock_in_request = Constants::DENIED;
                $job_details->clock_in_time = null;
                $job_details->clock_in_latitude = null;
                $job_details->clock_in_longitude = null;
                $job_details->update();
                return ResponseFormatter::successResponse("Clock-in rejected");
            }
            if ($approval == Constants::ACCEPTED) {
                $job_details->clock_in_request_accepted = Constants::ACCEPTED;
                $job_details->update();
                return ResponseFormatter::successResponse("Clock-in accepted");
            }
        }
    }

    public function clockOutRequest(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        else {
            $job = SecurityJob::where("id", $job_id)->first();
            $job_details = JobDetail::where("job_id", $job_id)
                ->where("clock_in_request_accepted", Constants::ACCEPTED)
                ->first();
            if ($job_details) {
                $extraTime = JobFunctions::extraTimeRequest($job_id);
                if ($extraTime) {
                    return ResponseFormatter::errorResponse("Customer requested you for 1 more hour.");
                } else {
                    $clock_out = JobFunctions::clockOutRequests($request, $job_details, $job);
                    if ($clock_out == true) {
                        return ResponseFormatter::successResponse("Clock-out request sent");
                    } else
                        return ResponseFormatter::errorResponse("Clock-out request sent but message couldn't be delivered");
                }
            } else {
                return ResponseFormatter::errorResponse("Can't clock-out");
            }
        }
    }

    public function clockOutResponseDetails(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::WEB_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");

        $job_details = JobDetail::where("job_id", $job_id)->first();
        if ($job_details) {
            if ($job_details->clock_out_request == 0) {
                return ResponseFormatter::errorResponse("Clock-out request not sent yet!");
            } else {
                $content_data = [
                    "job_id" => $job_details->job_id,
                    "job_clock_out_time" => Carbon::createFromTimestamp($job_details->clock_out_time)->format('Y-m-d\TH:i:s.uP'),
                    "job_clock_out_latitude" => $job_details->clock_out_latitude,
                    "job_clock_out_longitude" => $job_details->clock_out_longitude,
                ];
            }
            return ResponseFormatter::successResponse("Clock-out details", $content_data);
        } else {
            return ResponseFormatter::errorResponse("Clock-out details couldn't be fetched");
        }
    }

    public function clockOutResponse(Request $request, $job_id, $approval): \Illuminate\Http\JsonResponse
    {
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::WEB_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");

        else {
            $job_details = JobDetail::where("job_id", $job_id)
                ->where("clock_out_request", 1)
                ->first();
            if (!$job_details) {
                return ResponseFormatter::errorResponse("Clock-out request not sent yet!");
            }
            if ($approval == Constants::DENIED) {
                $job_details->clock_out_request = Constants::DENIED;
                $job_details->clock_out_time = null;
                $job_details->update();
                return ResponseFormatter::successResponse("Clock-out rejected");
            }
            if ($approval == Constants::ACCEPTED) {
                $jobs = SecurityJob::where("id", $job_id)->first();
                $jobs->job_status = Constants::COMPLETED;
                $job_details->clock_out_request_accepted = Constants::ACCEPTED;
                JobFunctions::transactions($jobs, $job_details);
                return ResponseFormatter::successResponse("Clock-out accepted");
            }
        }
    }
    public function clockOutResponseAdmin(Request $request, $job_id, $approval): \Illuminate\Http\JsonResponse
    {
        $auth_user = JobFunctions::authenticateUser($job_id, null, Constants::ADMIN_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");

        else {
            $job_details = JobDetail::where("job_id", $job_id)
                ->where("clock_out_request", 1)
                ->first();
            if (!$job_details) {
                return ResponseFormatter::errorResponse("Clock-out request not sent yet!");
            }
            if ($approval == Constants::DENIED) {
                $job_details->clock_out_request = Constants::DENIED;
                $job_details->clock_out_time = null;
                $job_details->update();
                return ResponseFormatter::successResponse("Clock-out rejected");
            }
            if ($approval == Constants::ACCEPTED) {
                $jobs = SecurityJob::where("id", $job_id)->first();
                $jobs->job_status = Constants::COMPLETED;
                $job_details->clock_out_request_accepted = Constants::ACCEPTED;
                JobFunctions::transactions($jobs, $job_details);
                return ResponseFormatter::successResponse("Clock-out accepted");
            }
        }
    }

    public function autoClockOut()
    {
        $job_details = JobDetail::where("clock_in_request_accepted", Constants::ACCEPTED)
            ->where("clock_out_request", Constants::INACTIVE)
            ->get();
        if ($job_details) {
            foreach ($job_details as $job_detail) {
                $jobs = SecurityJob::where("id", $job_detail->job_id)
                    ->where("event_end", "<", time())
                    ->whereIn('security_jobs.job_status', [1, 4])
//                    ->where("additional_hours_accepted", 0)
                    ->first();
                if ($jobs) {
                    $jobs->job_status = Constants::COMPLETED;
                    $job_detail->clock_in_request = Constants::ACCEPTED;
                    $job_detail->clock_in_request_accepted = Constants::ACCEPTED;
                    $job_detail->clock_out_request = Constants::ACCEPTED;
                    $job_detail->clock_out_request_accepted = Constants::ACCEPTED;
                    JobFunctions::transactions($jobs, $job_detail);
                }
            }
        }
    }

    public function requestMoreTime(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "extra_time" => "required|numeric|min:1|max:2",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::WEB_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        else {
            $job = SecurityJob::where("id", $job_id)
                ->where("job_status", Constants::UPCOMING)
                ->where("additional_hour_request", false)
                ->where("additional_hours_accepted", false)
                ->first();
            if ($job) {
                $job->additional_hour_request = Constants::ACTIVE;
                $job->additional_hours = $request->input("extra_time");
                $job->update();
                (new NotificationController())->addNotifications($job_id, $job->user_id, $job->security_jobs->guard_id,
                    5, StringTemplate::typeMessage(Constants::EXTRA_TIME, $job->event_name, null, $job->id));
                return ResponseFormatter::successResponse("Extra time request sent");
            } else {
                return ResponseFormatter::errorResponse("Extra time request already sent");
            }
        }
    }

    public function responseMoreTime(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "status" => "required|boolean",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        else {
            $job_details = JobDetail::where("job_id", $job_id)
                ->where("clock_in_request_accepted", Constants::ACCEPTED)
                ->first();
            $job = SecurityJob::where("id", $job_id)
                ->where("job_status", Constants::UPCOMING)
                ->where("additional_hour_request", true)
                ->where("additional_hours_accepted", false)
                ->first();
            if ($job) {
                if ($request->input("status") == 0) {
                    $job->additional_hours_accepted = Constants::REJECTED;
                    $job->update();
                    (new NotificationController())->addNotifications($job_id, $request->input(Constants::CURRENT_USER_ID_KEY),
                        $job->user_id, 7, StringTemplate::typeMessage(Constants::EXTRA_TIME_REJECTED, $job->event_name, null, $job->id));

                } else {
                    JobFunctions::checkAdditionalTime($job, $job_details, $job->additional_hours);
                    (new NotificationController())->addNotifications($job_id, $request->input(Constants::CURRENT_USER_ID_KEY),
                        $job->user_id, 6, StringTemplate::typeMessage(Constants::EXTRA_TIME_ACCEPTED, $job->event_name, null, $job->id));
                }

                return ResponseFormatter::successResponse("Extra time request status updated");
            } else {
                return ResponseFormatter::errorResponse("Extra time request could not be updated");
            }
        }
    }

    public function addIncidentReport(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $auth_user = JobFunctions::authenticateUser($job_id, $request->input(Constants::CURRENT_USER_ID_KEY), Constants::MOBILE_USER);
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
        else {
            $incident_report = new IncidentReport();
            $incident_report->job_id = $job_id;
            $incident_report->user_id = $request->input(Constants::CURRENT_USER_ID_KEY);
            $incident_report->name = $request->input("incident_name");
            $incident_report->message = $request->input("incident_message");
            if ($request->has("incident_image")) {
                $imageFileName = time() . '.' . $request->file('incident_image')->getClientOriginalExtension();
                $profile_image = $request->file("incident_image");
                $profile_image->storeAs('incident_image', $imageFileName, 's3');
                $incident_report->image = 'incident_image/' . $imageFileName;
            }
            $incident_report->save();
            return ResponseFormatter::successResponse("Incident report added");
        }
    }

    public function addJobReview(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "job_id" => "required",
            "rating" => "required|numeric|min:1|max:5",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        $job_review = JobReview::where("job_id", $request->input("job_id"))->first();
        if ($job_review) {
            return ResponseFormatter::errorResponse("Review already added");
        } else {
            $job_details = JobDetail::where("job_id", $request->input("job_id"))->first();
            $auth_user = JobFunctions::authenticateUser($request->input("job_id"), $request->input(Constants::CURRENT_USER_ID_KEY), Constants::WEB_USER);
            if (!$auth_user) {
                return ResponseFormatter::unauthorizedResponse("Unauthorized action!");
            }
            $is_job_completed = JobFunctions::jobCompleted($request->input("job_id"));
            if (!$is_job_completed) {
                return ResponseFormatter::errorResponse("Job has not completed yet!");
            } else {
                $review = new JobReview();
                $review->job_id = $request->input("job_id");
                $review->user_id = $job_details->guard_id;
                $review->rating = $request->input("rating");
                $review->message = $request->input("message");
                $review->save();
                return ResponseFormatter::successResponse("Job Review added");
            }
        }
    }

    public function transactions(): \Illuminate\Http\JsonResponse
    {
        $transactions = Transaction::orderBy("created_at", "DESC")->get();
        if ($transactions) {
            return ResponseFormatter::successResponse("Transactions", $transactions);
        } else {
            return ResponseFormatter::errorResponse("No transactions");
        }
    }
    public function updateTransactionStatus($job_id): \Illuminate\Http\JsonResponse
    {
        $transaction = Transaction::where("id", $job_id)->first();
        if ($transaction) {
            if ($transaction->status == 0) {
                $transaction->status = 1;
                $transaction->update();
            } else if ($transaction->status == 1) {
                $transaction->status = 0;
                $transaction->update();
            }
            return ResponseFormatter::successResponse("Transaction updated");
        } else {
            return ResponseFormatter::errorResponse("No transactions");
        }
    }

    public function reviewJob(Request $request, $job_id, $status): \Illuminate\Http\JsonResponse
    {
        $job = SecurityJob::where("job_status", Constants::PENDING)
            ->where('id', $job_id)
            ->first();
        if ($job) {
            $user = User::where("id", $job->user_id)->first();
            $job->job_status = $status;
            $job->update();
            if ($status == 0) {
                JobInformation::dispatch(
                    $user->email,
                    StringTemplate::typeMessage(Constants::MSG_JOB_ACCEPTED, $job->event_name, null, $job->id),
                );
                (new NotificationController())->addNotifications($job_id, $request->input(Constants::CURRENT_USER_ID_KEY),
                    $job->user_id, 9, StringTemplate::typeMessage(Constants::MSG_JOB_ACCEPTED, $job->event_name, null, $job->id));
            }
            if ($status == 7) {
                JobInformation::dispatch(
                    $user->email,
                    StringTemplate::typeMessage(Constants::MSG_JOB_REJECTED, $job->event_name, null, $job->id),
                );
                (new NotificationController())->addNotifications($job_id, $request->input(Constants::CURRENT_USER_ID_KEY),
                    $job->user_id, 10, StringTemplate::typeMessage(Constants::MSG_JOB_REJECTED, $job->event_name, null, $job->id));
            }
            return ResponseFormatter::successResponse("Jobs has been updated");
        } else {
            return ResponseFormatter::errorResponse("No Jobs");
        }
    }

    public function expireJobs(): \Illuminate\Http\JsonResponse
    {
        $jobs = SecurityJob::where('event_end', "<", time())
            ->whereIn("job_status", [Constants::OPEN, Constants::UPCOMING, Constants::PENDING, Constants::PENDING_ASSIGNMENT])
            ->get();

        if ($jobs) {
            foreach ($jobs as $job) {
                if ($job->job_status == 0 || $job->job_status == 6 || $job->job_status == 8) {
                    $job->job_status = Constants::EXPIRED;
                    $job->update();
                } else if ($job->job_status == 1) {
                    $job_details = JobDetail::where("job_id", $job->id)->first();
                    if ($job_details->clock_in_request == 0) {
                        $job->job_status = Constants::EXPIRED;
                        $job->update();
                    }
                }
            }
            return ResponseFormatter::successResponse("Jobs has been expired");
        } else {
            return ResponseFormatter::errorResponse("No Jobs");
        }
    }

}


