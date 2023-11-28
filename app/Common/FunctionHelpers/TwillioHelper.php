<?php
namespace App\Common\FunctionHelpers;

use Illuminate\Support\Facades\Config;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use Twilio\Rest\Client;

class TwillioHelper
{
    public static function generateTokenForIdentity($identity,$serviceId): string
    {
        // Required for all Twilio access tokens
        $twilioAccountSid = Config::get('constants.twilio_access');
        $twilioApiKey = Config::get('constants.twilio_api_key');
        $twilioApiSecret = Config::get('constants.twilio_secret_key');

        $serviceSid = $serviceId;;

        // Create access token, which we will serialize and send to the client
        $token = new AccessToken(
            $twilioAccountSid,
            $twilioApiKey,
            $twilioApiSecret,
            3600,
            $identity
        );

        // Create Chat grant
        $chatGrant = new ChatGrant();
        $chatGrant->setServiceSid($serviceSid);

        // Add grant to token
        $token->addGrant($chatGrant);
        // render token to string
        return $token->toJWT();
    }

    public static function createConversation($frendlyName): \Twilio\Rest\Conversations\V1\ConversationInstance
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $conversation = $twilio->conversations->v1->conversations
            ->create([
                    "friendlyName" => $frendlyName
                ]
            );

        return $conversation;
    }
    public static function updateConversation($frendlyName,$chat): \Twilio\Rest\Conversations\V1\ConversationInstance
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $conversation = $twilio->conversations->v1->conversations($chat)
            ->update([
                    "friendlyName" => $frendlyName
                ]
            );

        return $conversation;
    }

    public static function getConversationWithSid($sid): \Twilio\Rest\Conversations\V1\ConversationInstance
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $conversation = $twilio->conversations->v1->conversations($sid)
            ->fetch();

        return $conversation;
    }

    public static function deleteConversationWithSid($chat_id)
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $twilio->conversations->v1->conversations($chat_id)
            ->delete();
    }

    public static function addChatParticipantToConversation($identity, $first_name, $last_name, $user_profile, $conversationSid): ?string
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);
        $attribute = [
            "name" => $first_name . " " . $last_name,
            "profile_image" => $user_profile,
        ];

        $participant = $twilio->conversations->v1->conversations($conversationSid)
            ->participants
            ->create([
                    "identity" => $identity,
                    "attributes" => $attribute
                ]
            );

        return $participant->sid;
    }

    public static function getChatParticipantDetails($conversationSid, $participantSid): \Twilio\Rest\Conversations\V1\Conversation\ParticipantInstance
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $participant = $twilio->conversations->v1->conversations($conversationSid)
            ->participants($participantSid)
            ->fetch();

        return $participant;
    }

    public static function deleteChatParticipantFromConversation($conversationSid, $participantSid): bool
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $deleteUser = $twilio->conversations->v1->conversations($conversationSid)
            ->participants($participantSid)
            ->delete();

        return $deleteUser;
    }

    public static function getServiceSid($identity): ?string
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $services = $twilio->conversations->v1->services
            ->create($identity);

        return $services->sid;

    }

    public static function sendSms($phone_no,$text): \Twilio\Rest\Api\V2010\Account\MessageInstance
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);
        $data['from'] = Config::get('constants.twilio_phone');
        $data['body'] = $text;
        return $twilio->messages
            ->create($phone_no,$data);

//        return $message;
    }
}

?>
