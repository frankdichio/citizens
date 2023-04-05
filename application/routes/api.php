<?php

use App\Http\Controllers\CitizenController;
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

Route::controller(CitizenController::class)->group(function() {
    Route::post('/promote', 'promoteToInCharge');
    Route::post('/move', 'movingCitizenFromAFamilyToAnother');
    Route::post('/add', 'addCitizenToAnotherFamily');
    Route::post('/remove', 'removeCitizenFromAFamily');
});
