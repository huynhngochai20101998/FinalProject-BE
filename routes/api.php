<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\Post\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Topic\TopicController;
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

/**
 * Login & Register
 */
Route::post('login/', [LoginController::class, 'login']);
Route::post('register/', [RegisterController::class, 'register']);
Route::post('logout/', [LoginController::class, 'logout'])->middleware('auth:api');

/**
 * Auth
 */
Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::resource('posts', PostController::class)->only([
        'store', 'destroy', 'update'
    ]);
    Route::resource('topics', TopicController::class)->only([
        'store', 'destroy', 'update'
    ]);
});

/**
 * Verification Email
 */
Route::post('/email/verification-notification', [VerifyEmailController::class, 'reSendVerificationEmail'])->middleware('auth:api', 'throttle:6,1');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->name('verification.verify')->middleware(['auth:api', 'signed']);

/**
 * Reset Password
 */
Route::post('forgot-password', [ResetPasswordController::class, 'forgotPassword']);
Route::put('reset-password', [ResetPasswordController::class, 'reset']);

/**
 * Topic list
 */
Route::resource('topics', TopicController::class)->only(['index', 'show']);

/**
 * Post detail
 */
Route::resource('posts', PostController::class)->only(['show', 'index']);
