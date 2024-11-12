<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {

    /**
     * Auth Routes
     *
     * These routes handle user authentication, including login, registration, and logout.
    */
    Route::controller(AuthController::class)->group(function () {
        /**
         * Login Route
         *
         * @method POST
         * @route /v1/login
         * @desc Authenticates a user and returns a JWT token.
         */
        Route::post('login', 'login');

        /**
         * Register Route
         *
         * @method POST
         * @route /v1/register
         * @desc Registers a new user and returns a JWT token.
         */
        Route::post('register', 'register');

        /**
         * Logout Route
         *
         * @method POST
         * @route /v1/logout
         * @desc Logs out the authenticated user.
         * @middleware auth:api
         */
        Route::post('logout', 'logout')->middleware('auth:api');
    });

Route::apiResource('tasks',TaskController::class)->middleware('auth:api');
Route::post('tasks/{id}/attachments',[TaskController::class,'addAttachment'])->middleware('auth:api');
Route::put('tasks/{id}/status',[TaskController::class,'updateTaskStatus'])->middleware('auth:api');
Route::put('tasks/{id}/reassign',[TaskController::class,'reAssigne'])->middleware('auth:api');
Route::post('tasks/{id}/comments',[TaskController::class,'addComment'])->middleware('auth:api');
Route::get('reports/daily-tasks',[TaskController::class,'generateDailyReport'])->middleware('auth:api');
Route::post('/tasks/{task}/restore', [TaskController::class, 'restore']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('roles',[RoleController::class,'index']);
    Route::post('roles',[RoleController::class,'store']);
    Route::put('roles/{id}',[RoleController::class,'update']);
    Route::delete('roles/{id}',[RoleController::class,'destroy']);


});

});

