<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\TwillioHelper;
use App\Common\ResponseFormatter;
use App\Constants;
use App\Models\SecurityJob;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getToken(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        $security_job = SecurityJob::where("id", $job_id)
            ->orderBy("created_at", "ASC")->first();
        $user = User::where("id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($user) {
            try {
                $token = TwillioHelper::generateTokenForIdentity($user->friendly_name,
                    $security_job->chat_sid);
            } catch (\Exception $e) {
                return ResponseFormatter::errorResponse("Chat already created", $e);
            }
            $chatToken = [
                'token' => $token,
                'identifier' => $user->friendly_name,
            ];
            return ResponseFormatter::successResponse("Token", $chatToken);
        }
    }
}
