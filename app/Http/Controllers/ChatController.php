<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\TwillioHelper;
use App\Common\ResponseFormatter;
use App\Constants;
use App\Models\CustomerProfile;
use App\Models\JobDetail;
use App\Models\SecurityJob;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ChatController extends Controller
{
    public function getToken(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $s3SiteName = Config::get('constants.s3_bucket');
        $security_job = SecurityJob::where("id", $job_id)
            ->orderBy("created_at", "ASC")->first();
        $job_details = JobDetail::where("job_id", $job_id)->first();
        $user = User::where("id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($user) {
            try {
                $token = TwillioHelper::generateTokenForIdentity($user->friendly_name,
                    $security_job->chat_service_sid);
            } catch (\Exception $e) {
                return ResponseFormatter::errorResponse("Chat already created", $e);
            }
            if ($request->input(Constants::CURRENT_ROLE_ID_KEY) == Constants::MOBILE_USER) {
                $customer_details = CustomerProfile::where("user_id", $security_job->user_id)->first();
                $chatToken = [
                    'token' => $token,
                    'identifier' => $user->friendly_name,
                    'chat_id' => $security_job->chat_sid,
                    'job_posted_by_name' => $security_job->users->first_name . ' ' . $security_job->users->last_name,
                    'job_posted_by_image' => $customer_details->profile_image == null ? "" : $s3SiteName . $customer_details->profile_image,
                ];
            }
            if ($request->input(Constants::CURRENT_ROLE_ID_KEY) == Constants::WEB_USER) {
                $guard_details = UserProfile::where("user_id", $job_details->guard_id)->first();
                $chatToken = [
                    'token' => $token,
                    'identifier' => $user->friendly_name,
                    'chat_id' => $security_job->chat_sid,
                    'guard_name' => $security_job->users->first_name . ' ' . $security_job->users->last_name,
                    'guard_image' => $guard_details->profile_image == null ? "" : $s3SiteName . $guard_details->profile_image,
                ];
            }
            return ResponseFormatter::successResponse("Token", $chatToken);
        }
    }
}
