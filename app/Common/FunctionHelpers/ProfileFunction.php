<?php

namespace App\Common\FunctionHelpers;

use App\Common\ImageResize;
use App\Common\ConfigList;
use App\Common\ResponseFormatter;
use App\Http\Resources\ProfileResource;
use App\Models\Artist;
use App\Models\ProfileGenres;
use App\Models\ProfileInstruments;
use App\Models\ProfileTime;
use App\Models\ProfileVideos;
use App\Models\Song;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProfileFunction
{
    public static function getProfile($profiles, $currentUserSub)
    {
        $contentsDecoded = [];
        if ($profiles) {
            foreach ($profiles as $profileData) {
                $s3SiteName = Config::get('constants.s3_url');
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
                $profile_instruments = ProfileInstruments::where('user_id', '=', $profileData->user_id)->get();
                if ($profile_instruments) {
                    foreach ($profile_instruments as $profile_instrument) {
                        $profileInstruments[] = [
                            'instrument_id' => $profile_instrument->instrument_id,
                            'instrument_name' => $profile_instrument->instruments->name,
                            'experience' => $profile_instrument->experience,
                            'level_name' => $profile_instrument->instrument_level->name,
                            'level_id' => $profile_instrument->level
                        ];
                    }
                }
                $timeAvailable = [];
                $time_available = ProfileTime::where('user_id', $profileData->user_id)->get();
                if ($time_available) {
                    foreach ($time_available as $profile_time) {
                        $timeAvailable[] = [
                            'day_name' => ConfigList::dayString($profile_time->day),
                            'day_id' => $profile_time->day,
                            'from' => $profile_time->from_time,
                            'to' => $profile_time->to_time,
                        ];
                    }
                }
                $profileSongs = [];
                $profile_songs = Song::where('user_id', $profileData->user_id)->get();
                if ($profile_songs) {
                    foreach ($profile_songs as $profile_song) {
                        $profileSongs[] = [
                            'song_id' => $profile_song->song_id,
                            'song_name' => $profile_song->song_name,
                            'song_image' => $profile_song->song_image,
                            'album_name' => $profile_song->album_name,
                        ];
                    }
                }
                $profileArtist = [];
                $profile_artists = Artist::where('user_id', $profileData->user_id)->get();
                if ($profile_artists) {
                    foreach ($profile_artists as $profile_artist) {
                        $profileArtist[] = [
                            'artist_id' => $profile_artist->artist_id,
                            'artist_name' => $profile_artist->artist_name,
                            'artist_image' => $profile_artist->artist_image,
                            'artist_genre' => $profile_artist->genre_name,
                        ];
                    }
                }
                $profileVideo = [];
                $profile_videos = ProfileVideos::where('user_id', $profileData->user_id)->get();
                if ($profile_videos) {
                    foreach ($profile_videos as $profile_video) {
                        if ($currentUserSub != 1) {
                            $videoId = "";
                            $videoUrl = "";
                        } else {
                            $videoId = $profile_video->id;
                            $videoUrl = $s3SiteName . $profile_video->video_url;
                        }
                        $profileVideo[] = [
                            'video_id' => $videoId,
                            'video_url' => $videoUrl,
                            'thumbnail_url' => $s3SiteName . $profile_video->thumbnail_url,
                        ];
                    }
                }
                $newContent = [
                    'profile_id' => $profileData->user_id,
                    'profile_name' => $profileData->user->first_name . " " . $profileData->user->last_name,
                    'location' => $profileData->location,
                    'zipcode' => $profileData->zipcode,
                    'age' => $profileData->age,
                    'profile_image' => $profileData->profile_image == NULL ? "" : self::getImage($profileData->profile_image,$profileData->extensions),
                    'profile_bio' => $profileData->profile_bio,
                    'profile_visibility' => $profileData->profile_visibility,
                    'genres' => $profileGenres,
                    'instruments' => $profileInstruments,
                    'time_available' => $timeAvailable,
                    'profile_songs' => $profileSongs,
                    'profile_artists' => $profileArtist,
                    'profile_videos' => $profileVideo,
                ];
                $contentsDecoded[] = $newContent;
            }
            if (!$contentsDecoded) {
                return ResponseFormatter::errorResponse('Not found',[]);
                //return response()->json(["success" => false, "st+atus" => "ok", "data" => []]);
            } else {
                return ResponseFormatter::successResponse("Profile detail found.", $contentsDecoded);
                //return response()->json(["success" => true, "status" => "ok", "data" => $contentsDecoded]);
            }
        }
    }

    public static function getProfileList($profiles)
    {
        $contentsDecoded = [];
        if ($profiles) {
            foreach ($profiles as $profileData) {
                $s3SiteName = Config::get('constants.s3_url');
                $newContent = [
                    'profile_id' => $profileData->user_id,
                    'profile_name' => $profileData->user->first_name . " " . $profileData->user->last_name,
                    'profile_image' => $profileData->profile_image == NULL ? "" : self::getImageSmall($profileData->profile_image,$profileData->extensions),
                    'date_created' => self::dateFormat($profileData->user->email_verified_at),
                    'email' => $profileData->user->email,
                    'subscribed' => $profileData->user->subscribed == 1 ? "Upgraded" : "Basic",
                    'profile_visibility' => $profileData->profile_visibility,
                ];
                $contentsDecoded[] = $newContent;
            }
            if (!$contentsDecoded) {
                return ResponseFormatter::errorResponse('Not found', []);
                //return response()->json(["success" => false, "st+atus" => "ok", "data" => []]);
            } else {

                return ResponseFormatter::successResponse("Profile detail found.", $contentsDecoded);
            }
        }
    }

    public static function sendThumbnailToBucket()
    {
        $directory = '/jam-session/thumbnails';
        $files = Storage::disk('local')->allFiles($directory);
        foreach ($files as $file) {
            $file_name = str_replace('jam-session/thumbnails/', '', $file);
            $contents = Storage::get($file);
            $s3 = Storage::disk('s3');
            $s3->put('thumbnails/' . $file_name, $contents);
            Storage::disk("local")->delete($file);
        }
    }

    public static function sendImage($profile_image,$extension,$user) {
        $filePathName = 'profile_image/' . $user;
        for ($im= 1; $im <= 4; $im++) {
            $normal = Image::make($profile_image)->resize(ImageResize::sizeFormat($im),
                ImageResize::sizeFormat($im))->encode($extension);
            $imageFileName = ImageResize::sizeFormat($im) . 'x' . ImageResize::sizeFormat($im) .
                '.' . $profile_image->getClientOriginalExtension();
            $filePath = 'profile_image/' . $user . '/' . $imageFileName;
            $s3 = Storage::disk('s3');
            $s3->put($filePath, $normal->stream());
        }
//        UploadImage::dispatch($profile_image,$extension,$user);
        return $filePathName;
    }

    public static function getImage($profile_data,$extension): array
    {
        $s3SiteName = Config::get('constants.s3_url');
        return [
            '1' => $s3SiteName . $profile_data . '/' . '650x650.' . $extension,
            '2' => $s3SiteName . $profile_data . '/' . '500x500.' . $extension,
            '3' => $s3SiteName . $profile_data . '/' . '350x350.' . $extension,
            '4' => $s3SiteName . $profile_data . '/' . '150x150.' . $extension,
        ];
    }
    public static function getImageSmall($profile_data,$extension): string
    {
        $s3SiteName = Config::get('constants.s3_url');
        return $s3SiteName . $profile_data . '/' . '150x150.' . $extension;
    }
    public static function dateFormat($date)
    {
       return $date->format('d F Y');
    }
}
