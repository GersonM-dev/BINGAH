<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnakController;
use App\Http\Controllers\Api\DataAntropometryController;

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

// CRUD Anak
Route::get('/anaks', [AnakController::class, 'index']);
Route::post('/anaks', [AnakController::class, 'store']);
Route::get('/anaks/{anak}', [AnakController::class, 'show']);
Route::match(['put','patch'], '/anaks/{anak}', [AnakController::class, 'update']);
Route::delete('/anaks/{anak}', [AnakController::class, 'destroy']);

// Data antropometri bersarang di dalam Anak
Route::get('/anaks/{anak}/antropometries', [DataAntropometryController::class, 'index']);
Route::post('/anaks/{anak}/antropometries', [DataAntropometryController::class, 'store']);

// Operasi DataAntropometry individual
Route::get('/antropometries/{antropometry}', [DataAntropometryController::class, 'show']);
Route::match(['put','patch'], '/antropometries/{antropometry}', [DataAntropometryController::class, 'update']);
Route::delete('/antropometries/{antropometry}', [DataAntropometryController::class, 'destroy']);
