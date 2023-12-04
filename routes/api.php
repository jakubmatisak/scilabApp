<?php

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

Route::resource('experiments', ExperimentController::class)->except([
    'create', 'edit', 'update'
]);

Route::get('experiments/{id}/schemas', [ExperimentController::class, 'getSchemaFile']);
Route::delete('experiments/{id}', [ExperimentController::class, 'destroy']);

// Route::post('experiments', [ExperimentController::class, 'store']);
