<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('index');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

//php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider"

Route::group(['prefix' => 'api/v1'], function () {


    Route::get('authenticate/user', 'AuthenticateController@getAuthenticatedUser');

    Route::post('authenticate/register', 'AuthenticateController@register');

    Route::post('authenticate/resetPassword', 'AuthenticateController@resetEmail');

    Route::post('authenticate/resetPasswordConfirm/{token}', 'AuthenticateController@resetConfirm');

    Route::post('authenticate', 'AuthenticateController@authenticate');

    Route::get('authenticate', 'AuthenticateController@index');

});
