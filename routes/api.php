<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\Bitrix24WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {
	Route::post('/collect', [AnalyticsController::class, 'collect']);
	Route::get('/init/{projectId}', [AnalyticsController::class, 'init']);
	Route::get('/pixel/{projectId}', [AnalyticsController::class, 'pixel']);
});



Route::post('/webhooks/bitrix24/{project:uuid}', [Bitrix24WebhookController::class, 'handle'])
     ->name('webhooks.bitrix24.handle');

