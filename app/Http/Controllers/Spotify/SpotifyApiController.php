<?php

namespace App\Http\Controllers\Spotify;

use App\Common\ResponseFormatter;
use App\Constants;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use function response;

class SpotifyApiController extends Controller
{
    public function getToken()
    {
        $url = Config::get('constants.spotify_url');
        $response = Http::withHeaders(['Authorization' => 'Basic ' . base64_encode(Config::get('constants.client_id').":".Config::get('constants.client_secret'))])
            ->withOptions(['verify' => base_path('cacert.pem')])
            ->asForm()
            ->post($url, [
                'grant_type' => 'client_credentials'
            ]);
//        return response()->json($response->json());
        $data = response()->json($response->json());
//        return $data->getData()->access_token;
        $data->getData()->access_token;
        $txt = "";
        $txt .= $data->getData()->access_token;
        Storage::disk('local')->put('public/files/file.txt', $txt);
        return ResponseFormatter::successResponse("Spotify token created!",$txt);
//        $this->setEnv("SPOTIFY_TOKEN", $data->getData()->access_token);
    }

    private function setEnv($key, $value)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key . '=' . Config::get('constants.access_token'),
            $key . '=' . $value,
            file_get_contents(app()->environmentFilePath())
        ));
    }
}
