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
Route::post("/verify-account", [\App\Http\Controllers\AccountController::class, 'verifyAccount']);
Route::post("/send", [\App\Http\Controllers\UserController::class, 'sendEmail']);
Route::get("/refresh-token", [\App\Http\Controllers\LoginController::class, 'refreshToken'])->middleware("check_refresh_token");

Route::group(["middleware" => ["jwt.verify"]], function () {
    //Lists
    Route::get("/list/config", [\App\Http\Controllers\ListController::class, 'getAllLists']);
    Route::get("/list/firearms/{id}", [\App\Http\Controllers\ListController::class, 'getFireArms']);
    //Faqs
    Route::group(['prefix' => '/faq'], function () {
        Route::post("/", [\App\Http\Controllers\FaqController::class, 'addFaqs']);
        Route::get("/", [\App\Http\Controllers\FaqController::class, 'getFaqs']);
        Route::patch("/{id}", [\App\Http\Controllers\FaqController::class, 'updateFaqs']);
    });

    Route::group(["middleware" => ["rbac:user"]], function () {
        Route::group(['prefix' => '/user'], function () {
            Route::group(['prefix' => '/profile'], function () {
                Route::get("/", [\App\Http\Controllers\ProfileController::class, 'getUserProfile']);
                Route::get("/check-list", [\App\Http\Controllers\ProfileController::class, 'filledProfileList']);
                Route::post("/edit", [\App\Http\Controllers\ProfileController::class, 'editUserProfile']);
                Route::post("/image", [\App\Http\Controllers\ProfileController::class, 'editProfileImage']);
                Route::post("/personal-info/add", [\App\Http\Controllers\ProfileController::class, 'addPersonal']);
                Route::post("/personal-info/edit", [\App\Http\Controllers\ProfileController::class, 'editPersonal']);
                Route::post("/state-license/add", [\App\Http\Controllers\ProfileController::class, 'addStateLicense']);
                Route::post("/state-license/edit", [\App\Http\Controllers\ProfileController::class, 'editStateLicense']);
                Route::put("/bank-information/edit", [\App\Http\Controllers\ProfileController::class, 'addBanking']);
                Route::patch("/terms-and-condition", [\App\Http\Controllers\ProfileController::class, 'addDocument']);
            });
            Route::group(['prefix' => '/jobs'], function () {
                Route::get("/", [\App\Http\Controllers\SecurityJobController::class, 'selectedJobs']);
                Route::get("/view/{id}", [\App\Http\Controllers\SecurityJobController::class, 'getJobsById']);
                Route::get("/open", [\App\Http\Controllers\SecurityJobController::class, 'getOpenJobs']);
                Route::patch("/cancel/{id}", [\App\Http\Controllers\SecurityJobController::class, 'cancelJobs']);
                Route::patch("/update/{job_id}/{status}", [\App\Http\Controllers\SecurityJobController::class, 'updateJobStatus']);
                Route::patch("/clock-in-request/{job_id}", [\App\Http\Controllers\SecurityJobController::class, 'clockInRequest']);
                Route::post("/clock-out-request/{job_id}", [\App\Http\Controllers\SecurityJobController::class, 'clockOutRequest']);
                Route::post("/incident-report/{job_id}", [\App\Http\Controllers\SecurityJobController::class, 'addIncidentReport']);
                Route::post("/activity-log/{job_id}", [\App\Http\Controllers\ActivityReportController::class, 'addActivityReport']);
                Route::get("/activity-log/{job_id}", [\App\Http\Controllers\ActivityReportController::class, 'getActivityReport']);
            });
        });
        Route::get("/chat/token/{job_id}", [\App\Http\Controllers\ChatController::class, 'getToken']);
    });

    Route::group(["middleware" => ["rbac:customer,super_admin"]], function () {
        Route::group(['prefix' => '/web'], function () {
            Route::post("/profile/edit", [\App\Http\Controllers\ProfileController::class, 'editCustomerProfile']);
            Route::get("/profile", [\App\Http\Controllers\ProfileController::class, 'getCustomerProfile']);
            Route::group(['prefix' => '/jobs'], function () {
                Route::post("/", [\App\Http\Controllers\SecurityJobController::class, 'addJobs']);
                Route::get("/", [\App\Http\Controllers\SecurityJobController::class, 'getJobs']);
                Route::get("/{id}", [\App\Http\Controllers\SecurityJobController::class, 'getJobsById']);
                Route::patch("/cancel/{id}", [\App\Http\Controllers\SecurityJobController::class, 'cancelJobsCreated']);
                Route::patch("/clock-in-response/{job_id}/{approval}", [\App\Http\Controllers\SecurityJobController::class, 'clockInResponse']);
                Route::patch("/clock-out-response/{job_id}", [\App\Http\Controllers\SecurityJobController::class, 'clockOutResponse']);
            });
            Route::group(['prefix' => '/payment'], function () {
                Route::get("/ephemeral-key", [\App\Http\Controllers\PaymentController::class, 'getEphemeralKey']);
            });
            Route::group(['prefix' => '/card'], function () {
                Route::get("/list", [\App\Http\Controllers\PaymentController::class, 'getUserCard']);
                Route::delete("/delete/{card_id}", [\App\Http\Controllers\PaymentController::class, 'deleteCard']);
            });
        });
    });

    Route::group(["middleware" => ["rbac:super_admin"]], function () {
        Route::group(['prefix' => '/admin'], function () {
            Route::post("/deactivate-user", [\App\Http\Controllers\UserController::class, 'deactivateUser']);
            Route::patch("/account-status/{user_id}", [\App\Http\Controllers\AccountController::class, 'updateAccountStatus']);
            Route::post("/job-type", [\App\Http\Controllers\JobTypeController::class, 'addJobType']);
            Route::patch("/job-type/{id}", [\App\Http\Controllers\JobTypeController::class, 'editJobType']);
            Route::get("/job-type", [\App\Http\Controllers\JobTypeController::class, 'getAllJobTypes']);
            Route::get("/job-type/{id}", [\App\Http\Controllers\JobTypeController::class, 'getJobType']);

            Route::patch("/state/{id}", [\App\Http\Controllers\StateController::class, 'changeStateStatus']);

            Route::get("/jobs", [\App\Http\Controllers\SecurityJobController::class, 'getAllJobs']);
            Route::get("/jobs/{id}", [\App\Http\Controllers\SecurityJobController::class, 'getJobsById']);
        });
    });

    //Logout
    Route::post("/logout", [\App\Http\Controllers\LoginController::class, 'logout']);
});
