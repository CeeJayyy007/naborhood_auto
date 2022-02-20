<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::group([
    'prefix' => 'auth'
], function () {
    // login
    Route::post('login', 'Auth\AuthController@login');

    // register
    Route::post('register', 'Auth\AuthController@register');

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        //logout
        Route::get('logout', 'Auth\AuthController@logout');
        
        // edituser profile
        Route::post('edit-profile/{user_id}', 'UserController@editProfile');
       
        // get user profile
        Route::get('get-profile/{user_id}', 'UserController@getProfile');
       
        // get all user detail
        Route::get('get-all-detail', 'UserController@getAllUserDetail');
        
        // register new vehicle
        Route::post('new-vehicle', 'VehicleController@registerVehicle');

        // edit vehicle profile
        Route::post('edit-vehicle-profile/{user_id}', 'VehicleController@editVehicleProfile');
    });
    Route::get('/home', 'HomeController@index')->name('home');
});