<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\Post\PostController;
use App\Http\Controllers\Api\Schedule\ScheduleController;
use App\Http\Controllers\Api\Search\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Topic\TopicController;
use App\Http\Controllers\Api\Messages\GroupMessagesController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Models\User;
use App\Http\Controllers\Api\Group\GroupController;
use App\Http\Controllers\Api\Post\LikePostController;

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
 * Filter all user
 */
Route::get('users', function () {
    return User::all();
});

/**
 * Show profile
 */
Route::get('profile/user/{id}', [ProfileController::class, 'show_profile']);

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
    Route::resource('schedules', ScheduleController::class)->only([
        'store', 'destroy'
    ]);
    Route::resource('groups', GroupController::class)->only([
        'store', 'destroy', 'update', 'show', 'index'
    ]);
    /**
     * Profile
     */
    Route::post('profile/update-profile', [ProfileController::class, 'updateProfile']);
    Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
    /**
     * Comments
     */
    Route::resource('comments', CommentController::class)->only(['store', 'destroy', 'update']);
    Route::resource('groups', GroupController::class)->only([
        'destroy', 'update', 'show', 'index'
    ]);
    Route::post('/groups/add-members/', [GroupController::class, 'addMemberToGroup']);
    // dùng post thay delete vì post gửi được dataform
    Route::post('groups/{id}/remove-members', [GroupController::class, 'removeMemberFromGroup']);
    Route::post('post/{id}/remove-members', [PostController::class, 'removePostMember']);
    Route::post('/post/{id}/create-group/', [GroupController::class, 'createGroup']);

    Route::resource('messages', GroupMessagesController::class)->only([
        'store', 'index', 'destroy'
    ]);
});

/**
 * Verification Email
 */
Route::post('/email/verification-notification', [VerifyEmailController::class, 'reSendVerificationEmail'])
    ->middleware('auth:api', 'throttle:6,1');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->name('verification.verify');

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
Route::resource('posts', PostController::class)->only(['show']);
Route::get('post/search', [PostController::class, 'search']);

/**
 * Schedule
 */
Route::resource('schedules', ScheduleController::class)->only(['index']);
Route::post('schedule/check', [ScheduleController::class, 'checkSchedule'])->middleware('auth:api');

Route::get('comments/post/{postId}', [CommentController::class, 'getCommentsByPost']);

/**
 * Like & Dislike Post
 */
Route::post('post/{id}/like', [LikePostController::class, 'likeHandler'])->middleware('auth:api');
Route::post('post/{id}/dislike', [LikePostController::class, 'dislikeHandler'])->middleware('auth:api');
