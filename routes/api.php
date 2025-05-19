<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

// Route to check if the msisdn exists or not to the controller MsisdnController
Route::post('/send-msisdn', [\App\Http\Controllers\api\MsisdnController::class, 'sendMsisdn'])
    ->middleware(\App\Http\Middleware\ApiTokenMiddleware::class);

