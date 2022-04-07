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
        
        // upload user avatar
        Route::post('upload-avatar', 'UserController@uploadAvatar');
       
        // delete user avatar
        Route::get('delete-user-avatar/{user_id}', 'UserController@deleteUserAvatar');

        // delete user
        Route::get('delete-user/{user_id}', 'UserController@deleteUser');

        // register new vehicle
        Route::post('new-vehicle', 'VehicleController@registerVehicle');

        // edit vehicle profile
        Route::post('edit-vehicle-profile/{user_id}', 'VehicleController@editVehicleProfile');

        // delete vehicle
        Route::get('delete-vehicle/{vehicle_id}', 'VehicleController@deleteVehicle');

        // upload vehicle avatar
        Route::post('upload-vehicle-avatar', 'VehicleController@uploadAvatar');
       
        // delete vehicle avatar
        Route::get('delete-vehicle-avatar/{user_id}', 'VehicleController@deleteVehicleAvatar');

        // create service group
        Route::post('new-service-group', 'ServiceGroupController@newServiceGroup');

        // edit service group
        Route::post('edit-service-group/{service_group_id}', 'ServiceGroupController@editServiceGroup');

        // get all service groups
        Route::get('get-service-group', 'ServiceGroupController@getServiceGroup');

        // delete service group
        Route::get('delete-service-group/{service_group_id}', 'ServiceGroupController@deleteServiceGroup');

        // upload service group avatar
        Route::post('upload-group-avatar', 'ServiceGroupController@uploadAvatar');
       
        // delete service group avatar
        Route::get('delete-group-avatar/{user_id}', 'ServiceGroupController@deleteGroupAvatar');

        // create service
        Route::post('new-service', 'ServiceController@newService');

        // edit service
        Route::post('edit-service/{service_id}', 'ServiceController@editService');

        // get all service
        Route::get('get-service', 'ServiceController@getService');

        // get service
        Route::get('get-service/{service_group_id}', 'ServiceController@getService');
     
        // delete service
        Route::get('delete-service/{service_id}', 'ServiceController@deleteService');

    });
    Route::get('/home', 'HomeController@index')->name('home');
});