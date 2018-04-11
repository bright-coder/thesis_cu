<?php

use Illuminate\Http\Request;
use App\Http\Middleware\CheckAccessToken;
use App\Http\Middleware\CheckIsJson;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix'=>'v1','middleware' => [CheckIsJson::class, CheckAccessToken::class] ], function () {
    Route::resource('projects', 'Api\ProjectController');
    Route::resource('projects.databases', 'Api\DatabaseController');
    Route::resource('projects.functionalRequirements', 'Api\FunctionalRequirementController');
    Route::resource('projects.testCases', 'Api\TestCaseController');
    Route::resource('projects.RTM', 'Api\RTMController');
});
