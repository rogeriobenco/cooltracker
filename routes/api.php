<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingController as Tracking;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* Route::get('/{action}/{id?}', function ($action, $id = null) {
    return "Welcome API - {$action}" . (($id) ? ' => ' . $id : '');
}); */

//Route::get('/tracking/{arg?}', [Tracking::class, 'tracking']);
//Route::controller('tracking', 'TrackingController');

Route::controller(Tracking::class)->group(function () {
    Route::get('/tracking/{param?}/{debug?}/{local?}', 'tracking');
    Route::get('/update/{param?}', 'updateTracking');
    Route::get('/insert', 'insertTracking');
    Route::post('/insert', 'insertTracking');
    Route::get('/tracking-codes/{param?}/{debug?}/{local?}', 'userTrackingCodes');
    Route::get('/update-tracking-codes/{param?}/{debug?}/{local?}', 'updateTrackingCodes');
});