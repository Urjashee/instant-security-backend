<?php

namespace App\Http\Controllers\Spotify;

use App\Common\ResponseFormatter;
use App\Constants;
use App\Http\Controllers\Controller;
use GuzzleHttp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use function response;

class SpotifyArtistController extends Controller
{
    public function getArtist(Request $request) {
        $contentsDecoded = [];
        $name = $request->query("name");
        $type = $request->query("type");
        $offset = $request->query("offset");
        $url = "https://api.spotify.com/v1/search";
        $tokenData = Storage::disk('local')->get('public/files/file.txt');
        $client = new GuzzleHttp\Client(['verify' => base_path('cacert.pem')]);
        $response = $client->get($url, [
            "headers" => [
                'Authorization' => 'Bearer ' . $tokenData,
            ],
            "query" => [
                "q" => $name,
                "type" => $type,
                "offset" => $offset,
                "limit" => 50,
            ]
        ]);
        $data = $response->getBody();
//        return json_decode($response->getBody(),true);
        $artists = json_decode($data,true);
        foreach($artists['artists']['items'] as $items => $value)
        {
            $images = 'nil';
            if(!empty($value['images'][0]['url'])){
                $images = $value['images'][0]['url'];
            }
            $genres = '';
            foreach ($value['genres'] as $key => $value1) {
                if(!empty($genres)){
                    $genres = $genres.', '.$value1;
                }else{
                    $genres = $value1;
                }

            }
            $imageData = [
                'image' => $images,
                'id' => $value['id'],
                'name' => $value['name'],
                'artist_genre' => $genres,
            ];
            $contentsDecoded[] = $imageData;
        }
        return ResponseFormatter::successResponse("Artist found",$contentsDecoded);
    }

    public function getTrack(Request $request) {
        $contentsDecoded = [];
        $name = $request->query("name");
        $type = $request->query("type");
        $offset = $request->query("offset");
        $url = "https://api.spotify.com/v1/search";
        $tokenData = Storage::disk('local')->get('public/files/file.txt');
        $client = new GuzzleHttp\Client(['verify' => base_path('cacert.pem')]);
        $response = $client->get($url, [
            "headers" => [
                'Authorization' => 'Bearer ' . $tokenData,
            ],
            "query" => [
                "q" => $name,
                "type" => $type,
                "offset" => $offset,
                "limit" => 50,
            ]
        ]);
        $data = $response->getBody();
//        return json_decode($response->getBody(),true);
        $artists = json_decode($data,true);
        foreach($artists['tracks']['items'] as $items => $value)
        {
            $images = 'nil';
            if(!empty($value['album']['images'][2]['url'])){
                $images = $value['album']['images'][2]['url'];
            }
            $trackName = 'nil';
            if(!empty($value['album']['name'])){
                $trackName = $value['album']['name'];
            }
            $imageData = [
                'image' => $images,
                'id' => $value['id'],
                'name' => $value['name'],
                'album_name' => $trackName,
            ];
            $contentsDecoded[] = $imageData;
        }
        return ResponseFormatter::successResponse("track data",$contentsDecoded);
    }
}
