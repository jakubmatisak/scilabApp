<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExperimentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);

Route::middleware(['auth:sanctum', 'token.refresh'])->group(function () {
    Route::resource('experiments', ExperimentController::class)->except([
        'create', 'edit', 'update'
    ]);

    Route::get('experiments/{id}/schemas', [ExperimentController::class, 'getSchemaFile']);
    Route::delete('experiments/{id}', [ExperimentController::class, 'destroy']);
    Route::post('experiments/{id}/simulate', [ExperimentController::class, 'simulate']);
    Route::post('experiments/{id}', [ExperimentController::class, 'update']);

    Route::get('user/{id}', [UserController::class, 'show']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('user', [UserController::class, 'loggedInUser']);
});
