<?
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\VisitController;

Route::middleware('api')->group(function () {
	Route::post('/collect', [AnalyticsController::class, 'collect']);
	Route::get('/init/{projectId}', [AnalyticsController::class, 'init']);
	Route::get('/pixel/{projectId}', [AnalyticsController::class, 'pixel']);
});