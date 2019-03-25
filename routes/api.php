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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/signup', 'AuthController@signup');
Route::post('/signin', 'AuthController@signin');

Route::prefix('photos')->middleware('auth:api')->group(function() {
    Route::post('/store', 'PhotosController@create');
    Route::get('/', 'PhotosController@readAll');
    Route::get('/{id}', 'PhotosController@read');
    Route::put('/{id}', 'PhotosController@update');
    Route::delete('/{id}', 'PhotosController@delete');
});

Route::prefix('user')->middleware('auth:api')->group(function() {
    Route::get('/photos', 'UserController@getUserPhotos');
});