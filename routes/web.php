<?php

use App\Http\Controllers\HeController;
use App\Http\Controllers\PinController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HeController::class, 'index']);
Route::get('/verify', [HeController::class, 'verify']);
Route::get('/success', [HeController::class, 'success']);
Route::get('/failure', [HeController::class, 'failure']);
Route::post('/get-antifraud-script', [HeController::class, 'getAntiFraudScript']);
//savePreferredLanguage
Route::post('/save-preferred-language', [HeController::class, 'savePreferredLanguage']);
Route::get('/get-request-headers', [HeController::class, 'getRequestHeaders']);
Route::post('/handle-subscription', [HeController::class, 'handleSubscription']);
Route::post('/store-tracking', [HeController::class, 'storeTracking']);


// PIN
Route::get('/pin', [PinController::class, 'pin']);
Route::get('/otp', [PinController::class, 'otpVerification']);
Route::post('/pin-store-tracking', [PinController::class, 'storeTracking']);
Route::post('/pin-get-antifraud-script', [PinController::class, 'getAntiFraudScript']);
Route::post('/get-pin', [PinController::class, 'getPinCode']);
Route::post('/pin-handle-subscription', [PinController::class, 'handleSubscription']);
