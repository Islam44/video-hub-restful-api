<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::resource('videos', 'VideoController', ['only' => ['show', 'store','index']]);
Route::resource('tags', 'TagController');
Route::post('videos','VideoController@store');
Route::get('videos','VideoController@index');
Route::get('video/{video}','VideoController@show');
Route::get('video/thumb/{video}','VideoController@get_thumb');
Route::get('video/stream/{video}','VideoController@add_stream');
Route::get('video/mp3/{video}','VideoController@add_convert_to_audio');
Route::get('video/download/{video}','VideoController@download');
Route::post('video/search', 'VideoController@search');



