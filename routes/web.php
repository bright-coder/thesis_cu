<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('showTable','CoreController@index');

// Route::prefix('api/v1')->group(function () {
//     Route::get('project', 'ChangeRequestController@index');
//     Route::get('project/{id}', 'ChangeRequestController@show');
//     Route::post('project', 'ChangeRequestController@create');

// });
Auth::routes();

Route::get('/', 'HomeController@index')->name('home');

Route::get('project', "ProjectController@index")->name('project');
Route::get('project/create', "ProjectController@create")->name('projectCreate');
Route::get('project/{name}', "ProjectController@show")->name('projectShow');
Route::get('project/{name}/changeRequest', "ChangeRequestController@list")->name('changeRequestWithName');
Route::post('project', 'ProjectController@store');

Route::get('functionalrequirement', "ProjectController@index")->name('functionalrequirement');
Route::get('testcase', "ProjectController@index")->name('testcase');
Route::get('changeRequest', "ChangeRequestController@index")->name('changeRequest');
Route::get('changeRequest/create', "ChangeRequestController@create")->name('changeRequestCreate');
Route::get('project/{projectName}/changeRequest/{changeRequestId}', "ChangeRequestController@show")->name('changeRequestShow');
Route::get('RTM', "ProjectController@index")->name('rtm');
