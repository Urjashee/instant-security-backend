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
Route::post("/user/login", [\App\Http\Controllers\LoginController::class, 'login']);
Route::post("/web/login", [\App\Http\Controllers\LoginController::class, 'loginWeb']);
Route::post("/admin/login", [\App\Http\Controllers\LoginController::class, 'loginAdmin']);

Route::post("/user/register", [\App\Http\Controllers\UserController::class, 'addNewUser']);
Route::post("/web/register", [\App\Http\Controllers\UserController::class, 'addNewCustomer']);
Route::post("/forgot-password", [\App\Http\Controllers\ForgotPasswordController::class, 'resetPasswordRequest']);
Route::post("/reset-password", [\App\Http\Controllers\ForgotPasswordController::class, 'updatePassword']);
Route::post("/verify-account", [\App\Http\Controllers\UserController::class, 'verifyAccount']);
Route::post("/send", [\App\Http\Controllers\UserController::class, 'sendEmail']);
Route::get("/refresh-token", [\App\Http\Controllers\LoginController::class, 'refreshToken'])->middleware("check_refresh_token");

Route::group(["middleware" => ["jwt.verify"]], function () {
    //Lists
    Route::get("/list/config", [\App\Http\Controllers\ListController::class, 'getAllLists']);

    Route::group(["middleware" => ["rbac:user"]], function () {
        Route::group(['prefix' => '/user'], function () {
            Route::get("/profile-check-list", [\App\Http\Controllers\ProfileController::class, 'filledProfileList']);
            Route::post("/edit-profile", [\App\Http\Controllers\ProfileController::class, 'editUserProfile']);

            Route::post("/add-personal-info", [\App\Http\Controllers\ProfileController::class, 'addPersonal']);
            Route::post("/edit-personal-info", [\App\Http\Controllers\ProfileController::class, 'editPersonal']);

            Route::post("/add-state-license", [\App\Http\Controllers\ProfileController::class, 'addStateLicense']);
            Route::post("/edit-state-license", [\App\Http\Controllers\ProfileController::class, 'editStateLicense']);

            Route::put("/edit-bank-information", [\App\Http\Controllers\ProfileController::class, 'addBanking']);

            Route::patch("/terms-and-condition", [\App\Http\Controllers\ProfileController::class, 'addDocument']);

            Route::get("/profile", [\App\Http\Controllers\ProfileController::class, 'getUserProfile']);
        });

    });

    Route::group(["middleware" => ["rbac:customer"]], function () {
        Route::group(['prefix' => '/web'], function () {
            Route::post("/edit-profile", [\App\Http\Controllers\ProfileController::class, 'editCustomerProfile']);
            Route::get("/profile", [\App\Http\Controllers\ProfileController::class, 'getCustomerProfile']);
            Route::post("/jobs", [\App\Http\Controllers\SecurityJobController::class, 'addJobs']);
        });

    });

    Route::group(["middleware" => ["rbac:super_admin"]], function () {
        Route::group(['prefix' => '/admin'], function () {
            Route::post("/job-type", [\App\Http\Controllers\JobTypeController::class, 'addJobType']);
            Route::patch("/job-type/{id}", [\App\Http\Controllers\JobTypeController::class, 'editJobType']);
            Route::get("/job-type", [\App\Http\Controllers\JobTypeController::class, 'getAllJobTypes']);
            Route::get("/job-type/{id}", [\App\Http\Controllers\JobTypeController::class, 'getJobType']);

            Route::patch("/state/{id}", [\App\Http\Controllers\StateController::class, 'changeStateStatus']);
        });
    });

    //Logout
    Route::post("/logout", [\App\Http\Controllers\LoginController::class, 'logout']);
});
