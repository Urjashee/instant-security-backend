<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post("/login", [\App\Http\Controllers\LoginController::class, 'login']);
Route::post("/login-admin", [\App\Http\Controllers\LoginController::class, 'loginAdmin']);
Route::post("/register", [\App\Http\Controllers\UserController::class, 'addNewUser']);
Route::post("/forgot-password", [\App\Http\Controllers\ForgotPasswordController::class, 'resetPasswordRequest']);
Route::post("/reset-password", [\App\Http\Controllers\ForgotPasswordController::class, 'updatePassword']);
Route::post("/verify-account", [\App\Http\Controllers\UserController::class, 'verifyAccount']);
Route::post("/send", [\App\Http\Controllers\UserController::class, 'sendEmail']);
Route::get("/refresh-token", [\App\Http\Controllers\LoginController::class, 'refreshToken'])->middleware("check_refresh_token");

Route::group(["middleware" => ["jwt.verify"]], function () {
    //Spotify
    Route::post("/spotify/token", [\App\Http\Controllers\Spotify\SpotifyApiController::class, 'getToken']);
    Route::get("/spotify/artist", [\App\Http\Controllers\Spotify\SpotifyArtistController::class, 'getArtist']);
    Route::get("/spotify/track", [\App\Http\Controllers\Spotify\SpotifyArtistController::class, 'getTrack']);

    //Users
    Route::get("/users/info", [\App\Http\Controllers\UserController::class, 'getCurrentUserInfo']);

    //Lists
    Route::get("/list/instruments", [\App\Http\Controllers\ListController::class, 'getAllInstruments']);
    Route::get("/list/instruments-proficiency", [\App\Http\Controllers\ListController::class, 'getAllInstrumentsAndLevels']);
    Route::get("/list/genres", [\App\Http\Controllers\ListController::class, 'getAllGenres']);
    Route::get("/list/proficiency", [\App\Http\Controllers\ListController::class, 'getAllLevels']);
    Route::get("/list/config", [\App\Http\Controllers\ListController::class, 'getAllLists']);
    Route::get("/list/time", [\App\Http\Controllers\ListController::class, 'getAllTimes']);

    //Profile
//    Route::post("/profile/create", [\App\Http\Controllers\ProfileController::class, 'addProfile']);
//    Route::get("/profile/current", [\App\Http\Controllers\ProfileController::class, 'getCurrentProfile']);
//    Route::post("/profile/edit", [\App\Http\Controllers\ProfileController::class, 'updateProfile']);
//    Route::get("/profile/{id}", [\App\Http\Controllers\ProfileController::class, 'getProfilesById']);
//    Route::get("/profiles/active", [\App\Http\Controllers\ProfileController::class, 'getAllActiveProfiles']);
//    Route::patch("/profile/toggle", [\App\Http\Controllers\ProfileController::class, 'toggleProfileUser']);
//    Route::post("/profile/video", [\App\Http\Controllers\ProfileController::class, 'profileVideoUpload']);
//    Route::delete("/profile/video/{id}", [\App\Http\Controllers\ProfileController::class, 'deleteProfileVideo']);
//    Route::get("/thumbnails", [\App\Http\Controllers\ProfileController::class, 'sendThumbnailToBucket']);

    //Location
//    Route::patch("/location/update", [\App\Http\Controllers\ProfileController::class, 'updateCurrentLocation']);
//
//    //JamSessions
//    Route::post("/jam-session/create", [\App\Http\Controllers\JamSessionController::class, 'addJamSession']);
//    Route::get("/jam-sessions", [\App\Http\Controllers\JamSessionController::class, 'getJamSession']);
//    Route::get("/jam-session/list", [\App\Http\Controllers\JamSessionController::class, 'getJamSessionNames']);
//    Route::get("/jam-session/{id}", [\App\Http\Controllers\JamSessionController::class, 'getJamSessionById']);
//    Route::get("/jam-session/profiles/{id}/{instrument}", [\App\Http\Controllers\JamSessionController::class, 'getActiveProfiles']);
//    Route::patch("/jam-session/send-request/{id}", [\App\Http\Controllers\JamSessionController::class, 'addInstrumentProfile']);
//    Route::patch("/jam-session/{id}", [\App\Http\Controllers\JamSessionController::class, 'updateJamSession']);
//    Route::patch("/jam-session/request-update/{jam_id}/{profile_id}", [\App\Http\Controllers\JamSessionController::class, 'acceptedJamProfile']);
//    Route::patch("/jam-session/remove-member/{jam_id}/{profile_id}", [\App\Http\Controllers\JamSessionController::class, 'removeMemberFromJam']);


    //Logout
    Route::post("/logout", [\App\Http\Controllers\LoginController::class, 'logout']);

    Route::group(["middleware" => ["rbac:admin"]], function () {
        Route::get("/users", [\App\Http\Controllers\UserController::class, 'getAllUsers']);
        Route::get("/user/{id}", [\App\Http\Controllers\UserController::class, 'getUser']);
        Route::get("/users/roles", [\App\Http\Controllers\UserController::class, 'getRoles']);
      });
});
