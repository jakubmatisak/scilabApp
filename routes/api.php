<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExperimentController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('experiments', ExperimentController::class)->except([
        'create', 'edit', 'update'
    ]);

    Route::get('experiments/{id}/schemas', [ExperimentController::class, 'getSchemaFile']);
    Route::delete('experiments/{id}', [ExperimentController::class, 'destroy']);
    Route::post('experiments/{id}/simulate', [ExperimentController::class, 'simulate']);
});
