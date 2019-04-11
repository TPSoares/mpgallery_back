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
    Route::get('/offset/{offset}', 'PhotosController@readAll');
    Route::get('/{id}', 'PhotosController@read');
    Route::put('/{id}', 'PhotosController@update');
    Route::delete('/{id}', 'PhotosController@delete');
    //Likes session
    Route::post('/{id}/like', 'LikesController@create');
    Route::get('/{id}/like', 'LikesController@read');
    Route::delete('/{id}/like', 'LikesController@delete');
    //Comments session
    Route::post('/{id}/comment', 'CommentsController@create');
    Route::get('/{id}/comment/offset/{offset}/limit/{limit}', 'CommentsController@read');
    Route::put('/{id}/comment/{commentId}', 'CommentsController@update');
    Route::delete('/{id}/comment/{commentId}', 'CommentsController@delete');
    //User
    Route::get('/user/{user_id}', 'UserController@getUser');
});

Route::prefix('user')->middleware('auth:api')->group(function() {
    Route::get('/photos', 'UserController@getUserPhotos');
});