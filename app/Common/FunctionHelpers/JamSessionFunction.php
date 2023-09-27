<?php

namespace App\Common\FunctionHelpers;

use App\Common\ResponseFormatter;
use App\Http\Controllers\NotificationController;
use App\Models\CurrentLocations;
use App\Models\Genre;
use App\Models\Instruments;
use App\Models\JamSessionProfiles;
use App\Models\JamSessions;
use App\Models\Profile;
use App\Models\ProfileGenres;
use App\Models\ProfileInstruments;
use App\Models\ProfileTime;
use App\Models\RejectedUsers;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class JamSessionFunction
{
    public static function authenticateUser($type, $jam, $user)
    {
        $count = 0;
        if ($type == 1) {
            $jamSessions = JamSessions::where('id', $jam)->first();
            if ($jamSessions->user_id == $user)
                return (true);
            else
                return (false);
        }
        if ($type == 2) {
            $jamSessions = JamSessions::where('id', $jam)->first();
            if ($jamSessions->user_id == $user) {
                $count++;
            }
            $jamSessionUsers = JamSessionProfiles::where('jam_session_id', $jam)->get();
            foreach ($jamSessionUsers as $jamSessionUser) {
                if ($jamSessionUser->profile_id == $user) {
                    $count++;
                }
            }
            if ($count == 0) {
                return (false);
            } else {
                return (true);
            }
        }
    }

    public static function updateRejectedUsersList($profile_id, $jam_id, $instrument_id): bool
    {
        $newRejectedUser = new RejectedUsers();
        $newRejectedUser->user_id = $profile_id;
        $newRejectedUser->jam_id = $jam_id;
        $newRejectedUser->instrument_id = $instrument_id;
        $newRejectedUser->save();
        return (true);
    }

    public static function jamSession($jamSessions, $userProfileId)
    {
        $s3SiteName = Config::get('constants.s3_url');
        $contentsDecoded = [];
        $jamSessionMembers = [];
        if ($jamSessions) {
            foreach ($jamSessions as $jamSession) {
                $jamCreatorProfileInstruments = [];
                $profileInstrument = ProfileInstruments::where("user_id", $jamSession->user->id)->first();
                $profileDetail = Profile::where("user_id", $jamSession->user->id)->first();
                if ($profileInstrument) {
                    $jamCreatorProfileInstruments[] = [
                        'jam_organizer_id' => $jamSession->user->id,
                        'jam_organizer_image' => $profileDetail->profile_image == NULL ? "" : ProfileFunction::getImage($profileDetail->profile_image,$profileDetail->extensions),
                        'jam_organizer_name' => $jamSession->user->id == $userProfileId ? "You" : $jamSession->user->first_name . ' ' . $jamSession->user->last_name,
                        'instrument_id' => $profileInstrument->instruments->id,
                        'instrument_name' => $profileInstrument->instruments->name,
                        'instrument_level' => $profileInstrument->instrument_level->name,
                        'instrument_experience' => $profileInstrument->experience
                    ];
                }
                $openPosition = 0;
                $filledPosition = 0;
                $jamSessionsId = $jamSession->id;
                $jamSessionProfiles = JamSessionProfiles::where("jam_session_id", $jamSessionsId)->get();
                foreach ($jamSessionProfiles as $jamSessionProfile) {
                    if ($jamSessionProfile->accepted_status == 0) {
                        $openPosition++;
                    } else {
                        $filledPosition++;
                    }
                }
                $jamSessionGenres = [];
                $jamSessionInstruments = [];
                $genreArrays = explode(",", $jamSession->genres);
                foreach ($genreArrays as $genreArray) {
                    $jam_session_genres = Genre::where("id", $genreArray)->first();
                    if ($jam_session_genres) {
                        $jamSessionGenres[] = [
                            'genre_id' => $jam_session_genres->id,
                            'genre_name' => $jam_session_genres->name
                        ];
                    }
                }
                $jamSessionsInstruments = JamSessionProfiles::where("jam_session_id", $jamSessionsId)->get();
                foreach ($jamSessionsInstruments as $jamSessionsInstrument) {
                    $acceptedStatus = null;
                    $jam_session_instruments = Instruments::where("id", $jamSessionsInstrument->instrument_id)->first();
                    if ($jam_session_instruments) {
                        if (($jamSessionsInstrument->accepted_status == 0) && ($jamSessionsInstrument->profile_id == NULL)) {
                            $acceptedStatus = "Open";
                        }
                        if ($jamSessionsInstrument->accepted_status == 1) {
                            $acceptedStatus = "Filled";
                        }
                        if (($jamSessionsInstrument->accepted_status == 0) && ($jamSessionsInstrument->profile_id != NULL)) {
                            $acceptedStatus = "Pending";
                        }
                        $jam_session_profile = User::where("id", $jamSessionsInstrument->profile_id)->first();
                        if ($jamSessionsInstrument->profile_id === null) {
                            $firstLastName = "-";
                            $profileId = "-";
                        } else {
                            if ($jamSessionsInstrument->profile_id == $userProfileId) {
                                $firstLastName = "You";
                            } else {
                                $firstLastName = $jam_session_profile->first_name . " " . $jam_session_profile->last_name;
                            }
                            $profileId = $jamSessionsInstrument->profile_id;
                            $jamSessionMembers[] = [
                                'id' => $jamSessionsInstrument->id,
                                'profile_id' => $profileId,
                                'instrument_id' => $jam_session_instruments->id,
                                'instrument_name' => $jam_session_instruments->name,
                                'instrument_experience' => self::instrumentDetails($jam_session_instruments->id, $profileId, 1),
                                'instrument_proficiency' => self::instrumentDetails($jam_session_instruments->id, $profileId, 2),
                                'profile_name' => $firstLastName,
                                'profile_image' => self::getProfileImage($s3SiteName, $profileId == NULL ? "-" : $profileId),
                            ];
                        }
                        $jamSessionInstruments[] = [
                            'id' => $jamSessionsInstrument->id,
                            'profile_id' => $profileId,
                            'instrument_id' => $jam_session_instruments->id,
                            'instrument_name' => $jam_session_instruments->name,
                            'accepted_status' => $acceptedStatus,
                            'profile_name' => $firstLastName,
                        ];
                    }
                }
//                $jamSessionMembers[] = [
//                    'id' => '-',
//                    'profile_id' => $jamSession->user->id,
//                    'instrument_id' => $profileInstrument->instruments->id,
//                    'instrument_name' => $profileInstrument->instruments->name,
//                    'instrument_experience' => $this->instrumentDetails($profileInstrument->instruments->id, $jamSession->user->id, 1),
//                    'instrument_proficiency' => $this->instrumentDetails($profileInstrument->instruments->id, $jamSession->user->id, 2),
//                    'profile_name' => $jamSession->user->id != $userProfileId ? $jamSession->user->first_name . ' ' . $jamSession->user->last_name: "You",
//                    'profile_image' => $this->getProfileImage($s3SiteName, $jamSession->user->id),
//                ];
                $newContent = [
                    'jam_name' => $jamSession->jam_name,
                    'jam_session_id' => $jamSessionsId,
                    'is_organizer' => $jamSession->user->id == $userProfileId ? true : false,
                    'created_date' => $jamSession->created_at,
                    'jam_organizer' => $jamCreatorProfileInstruments,
                    'instruments' => $jamSessionInstruments,
                    'members' => $jamSessionMembers,
                    'genres' => $jamSessionGenres,

                ];
                $contentsDecoded[] = $newContent;
            }
            if (!$contentsDecoded) {
                return ResponseFormatter::errorResponse('Not found');
                //return response()->json(["success" => false, "status" => "ok", "data" => []]);
            } else {
                return ResponseFormatter::successResponse("", $contentsDecoded);
                //return response()->json(["success" => true, "status" => "ok", "data" => $contentsDecoded]);
            }
        }
    }

    public static function instrumentDetails($instrument, string $profileId, $type)
    {
        $profileInstruments = ProfileInstruments::where("user_id", $profileId)
            ->where('instrument_id', $instrument)->get();
        foreach ($profileInstruments as $profileInstrument) {
            return ($type == 1 ? $profileInstrument->experience : $profileInstrument->instrument_level->name);
        }
    }

    public static function getProfileImage($link, string $profileId)
    {
        if ($profileId == "-") {
            return ("");
        } else {
            $profileDetail = Profile::where("user_id", $profileId)->first();
            return ($profileDetail->profile_image != NULL ? ProfileFunction::getImage($profileDetail->profile_image,$profileDetail->extension) : "-");
        }
    }

    public static function getProfile($profiles, $rejectedProfileList, $jam_id, $currentUser,
                                $radius, $genre, $experience, $min_age, $max_age, $proficiency, $time, $instrument)
    {
        $s3SiteName = Config::get('constants.s3_url');
        $contentsDecoded = [];
        $radiusSearch = [];
        $genreSearch = [];
        $experienceSearch = [];
        $ageSearch = [];
        $proficiencySearch = [];
        $timeSearch = [];
        $acceptedList = [];
        if ($profiles) {
            foreach ($profiles as $profileData) {
                $rejectedList = RejectedUsers::where("jam_id", $jam_id)
                    ->where("user_id", $profileData->user_id)
//                    ->where("instrument_id", $instrument)
                    ->first();
                if ($rejectedList) {
                    $rejectedProfileList[] = $rejectedList->user_id;
                }
                if (!in_array($profileData->user_id, $rejectedProfileList)) {
                    $acceptedList[] = $profileData->user_id;
                }
//                filters will go over here
                if ($radius != null) {
                    $radiusSearch = self::userByRadius($acceptedList, $radius, $currentUser);
                    $acceptedList = [];
                    $acceptedList = $radiusSearch;
                }
                if ($genre != null) {
                    $genreSearch = self::userByGenres($acceptedList, $genre);
                    $acceptedList = [];
                    $acceptedList = $genreSearch;
                }
                if ($experience != null) {
                    $experienceSearch = self::userByExperience($acceptedList, $experience, $instrument);
                    $acceptedList = [];
                    $acceptedList = $experienceSearch;
                }
                if ($min_age != null) {
                    $ageSearch = self::userByAge($acceptedList, $min_age, $max_age);
                    $acceptedList = [];
                    $acceptedList = $ageSearch;
                }
                if ($proficiency != null) {
                    $proficiencySearch = self::userByProficiency($acceptedList, $proficiency, $instrument);
                    $acceptedList = [];
                    $acceptedList = $proficiencySearch;
                }
                if ($time != null) {
                    $timeSearch = self::userByTime($acceptedList, $time);
                    $acceptedList = [];
                    $acceptedList = $timeSearch;
                }
                if (in_array($profileData->user_id, $acceptedList)) {
                    $profileGenres = [];
                    $profile_genres = ProfileGenres::where('user_id', '=', $profileData->user_id)->get();
                    if ($profile_genres) {
                        foreach ($profile_genres as $profile_genre) {
                            $profileGenres[] = [
                                'genre_id' => $profile_genre->genre_id,
                                'genre_name' => $profile_genre->genres->name
                            ];
                        }
                    }
                    $profileInstruments = [];
                    $profile_instruments = ProfileInstruments::where('user_id', '=', $profileData->user_id)
                        ->where('instrument_id', $instrument)
                        ->get();
                    if ($profile_instruments) {
                        foreach ($profile_instruments as $profile_instrument) {
                            $instrument_id = $profile_instrument->instrument_id;
                            $instrument_name = $profile_instrument->instruments->name;
                            $instrument_experience = $profile_instrument->experience;
                            $level_name = $profile_instrument->instrument_level->name;
                            $level_id = $profile_instrument->level;
                        }
                    }
                    $newContent = [
                        'profile_id' => $profileData->user_id,
                        'profile_name' => $profileData->user->first_name . " " . $profileData->user->last_name,
                        'age' => $profileData->age,
                        'profile_image' => $profileData->profile_image == NULL ? "" : ProfileFunction::getImage($profileData->profile_image,$profileData->extensions),
                        'genres' => $profileGenres,
                        'instrument_id' => $instrument_id,
                        'instrument_name' => $instrument_name,
                        'experience' => $instrument_experience,
                        'level_name' => $level_name,
                        'level_id' => $level_id
                    ];
                    $contentsDecoded[] = $newContent;
                }
            }
        }
        if (!$contentsDecoded) {
            return ResponseFormatter::errorResponse('Not found.');
            //return response()->json(["success" => false, "status" => "ok", "data" => []]);
        } else {
            return ResponseFormatter::successResponse("", $contentsDecoded);
            //return response()->json(["success" => true, "status" => "ok", "data" => $contentsDecoded]);
        }
    }

    public static function userByRadius($acceptedList, $radius, $currentUser)
    {
        $usersLocation = CurrentLocations::where('user_id', $currentUser)->first();
        $distance = $radius;
        $contentsDecoded = [];
        $users = CurrentLocations::select("user_id")
            ->distance($usersLocation->latitude, $usersLocation->longitude, $distance, "m")
            ->orderby("distance", "desc")
            ->get();
        foreach ($users as $user) {
            if (in_array($user->user_id, $acceptedList, TRUE)) {
                $contentsDecoded[] = $user->user_id;
            }
        }
        return $contentsDecoded;
//        return response()->json(["success" => true, "status" => "ok", "data" => $contentsDecoded]);
    }

    public static function userByGenres(array $acceptedLists, $genre)
    {
        $contentsDecoded = [];
        $genres = explode(",", $genre);
        foreach ($acceptedLists as $acceptedList) {
            $profilesGenres = ProfileGenres::where('user_id', $acceptedList)->get();
            if ($profilesGenres) {
                foreach ($profilesGenres as $profilesGenre) {
                    if (in_array($profilesGenre->genre_id, $genres)) {
                        $contentsDecoded[] = $profilesGenre->user_id;
                    }
                }
            }
        }
        return array_unique($contentsDecoded);
//        return response()->json(["success" => true, "status" => "ok", "data" => $contentsDecoded]);
    }

    public static function userByExperience(array $acceptedLists, $experience, $instrument_id)
    {
        $contentsDecoded = [];
        foreach ($acceptedLists as $acceptedList) {
            $profilesInstruments = ProfileInstruments::where('user_id', $acceptedList)
                ->where("instrument_id", $instrument_id)
                ->get();
            if ($profilesInstruments) {
                foreach ($profilesInstruments as $profilesInstrument) {
                    if ($profilesInstrument->experience >= $experience) {
                        $contentsDecoded[] = $profilesInstrument->user_id;
                    }
                }
            }
        }
        return array_unique($contentsDecoded);
    }

    public static function userByAge(array $acceptedLists, $min_age, $max_age)
    {
        $contentsDecoded = [];
        foreach ($acceptedLists as $acceptedList) {
            $profilesAges = Profile::where('user_id', $acceptedList)->get();
            if ($profilesAges) {
                foreach ($profilesAges as $profilesAge) {
                    if ($profilesAge->age >= $min_age && $profilesAge->age <= $max_age ) {
                        $contentsDecoded[] = $profilesAge->user_id;
                    }
                }
            }
        }
        return array_unique($contentsDecoded);
    }

    public static function userByProficiency(array $acceptedLists, $proficiency, $instrument_id)
    {
        $contentsDecoded = [];
        $proficiencies = explode(",", $proficiency);
        foreach ($acceptedLists as $acceptedList) {
            $profilesLevels = ProfileInstruments::where('user_id', $acceptedList)
                ->where("instrument_id", $instrument_id)
                ->get();
            if ($profilesLevels) {
                foreach ($profilesLevels as $profilesLevel) {
                    if (in_array($profilesLevel->level, $proficiencies)) {
                        $contentsDecoded[] = $profilesLevel->user_id;
                    }
//                    if ($profilesLevel->level == $proficiency) {
//                        $contentsDecoded[] = $profilesLevel->user_id;
//                    }
                }
            }
        }
        return array_unique($contentsDecoded);
    }

    public static function userByTime(array $acceptedLists, $time)
    {
        $contentsDecoded = [];
        foreach ($acceptedLists as $acceptedList) {
            $profilesTimes = ProfileTime::where('user_id', $acceptedList)->get();
            if ($profilesTimes) {
                foreach ($profilesTimes as $profilesTime) {
                    if (($profilesTime->day == $time[0]) &&
                        gmdate("H:i:s", $time[1]) >= gmdate("H:i:s", $profilesTime->from_time) &&
                        gmdate("H:i:s", $time[2]) <= gmdate("H:i:s", $profilesTime->to_time)) {
                        $contentsDecoded[] = $profilesTime->user_id;
                    }
                }
            }
        }
        return array_unique($contentsDecoded);
    }

    public static function jamSessionProfile($jam, $profile, $status, $type)
    {
        $countProfiles = 0;
        $jamSessions = JamSessionProfiles::where("jam_session_id", $jam)
            ->where("profile_id", $profile)
            ->first();
        if ($jamSessions) {
            $jamSessions->accepted_status = $status;
            if ($type == 3) {
                $jamSessions->profile_id = NULL;
                $jamSessions->accepted_status = 0;
                $jamSessions->chat_sid = NULL;
                $jamSessions->participant_id = NULL;
            }
            $jamSessions->update();
            $jamSessions->refresh();
            $jamData = JamSessions::where("id", $jam)->first();
            (new NotificationController())->sendNotifications($jamData->user_id, $profile,
                $jamData->user_id, $jamData->id, $jamSessions->instrument_id, $type);
            if ($type == 2) {
                if ($jamData->chat_status == 1) {
                    (new NotificationController())->sendNotifications($jamData->user_id, $profile,
                        $profile, $jamData->id, $jamSessions->instrument_id, 5);
                    $participant = TwillioHelper::addChatParticipantToConversation($jamData->user->friendly_name, $jamData->chat_sid);
                    $jamSessionDetails = JamSessionProfiles::where("jam_session_id", $jam)
                        ->where('profile_id',$profile)
                        ->first();
                    $jamSessionDetails->chat_sid = $jamData->chat_sid;
                    $jamSessionDetails->participant_id = $participant;
                    $jamSessionDetails->update();
                    return 1;
                }
                if ($jamData->chat_status == 0) {
                    $jamSessionCount = JamSessionProfiles::where("jam_session_id", $jam)->get();
                    foreach ($jamSessionCount as $count) {
                        if ($count->accepted_status == 0) {
                            $countProfiles++;
                        }
                    }
                    if ($countProfiles <= 0) {
                        $jamSession = JamSessions::where("id", $jam)->first();
                        $jamSession->chat_status = 1;
                        $jamSession->update();
                        foreach ($jamSessionCount as $count) {
                            (new NotificationController())->sendNotifications($jamData->user_id, $profile,
                                $count->profile_id, $jamData->id, $count->instrument_id, 6);
                            //add Participants to Conversation
                            $participant = TwillioHelper::addChatParticipantToConversation($count->user->friendly_name, $jamSession->chat_sid);
                            $count->chat_sid = $jamSession->chat_sid;
                            $count->participant_id = $participant;
                            $count->update();

                        }
                    }
                }
                return 1;
            }
            if ($type == 3) {
                $rejectedUser = JamSessionFunction::updateRejectedUsersList($profile, $jamData->id, $jamSessions->instrument_id);
                if ($rejectedUser == true) {
                    return 1;
                }
            }
        } else {
            return ResponseFormatter::errorResponse("No such record");
        }
    }

    public static function rejectUsers()
    {
        $jamSessions = JamSessionProfiles::where('updated_at', '<', Carbon::now()->subHours(72)->toDateTimeString())
            ->where('profile_id', '!=', NULL)
            ->where('accepted_status', '==', 0)
            ->get();
        foreach ($jamSessions as $jamSession) {
            if ($jamSession->accepted_status == 0) {
                $profile = $jamSession->profile_id;
                $jamSession->profile_id = NULL;
                $jamSession->update();
                $jamSession->refresh();
                $rejectedUser = JamSessionFunction::updateRejectedUsersList($profile, $jamSession->jam_session_id, $jamSession->instrument_id);
                if ($rejectedUser == true) {
                    $jam = JamSessions::where('id', $jamSession->jam_session_id)->first();
                    (new NotificationController())->sendNotifications($jam->user_id, $profile,
                        $jam->user_id, $jamSession->jam_session_id, $jamSession->instrument_id, 3);
                }
            }
        }
    }
}
