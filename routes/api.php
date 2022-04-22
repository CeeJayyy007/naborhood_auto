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

        // create service
        Route::post('new-service', 'ServiceController@newService');

        // edit service
        Route::post('edit-service/{service_id}', 'ServiceController@editService');

        // get all service
        Route::get('get-all-services', 'ServiceController@getAllServices');

        // get service
        Route::get('get-service/{service_group_id}', 'ServiceController@getService');
     
        // delete service
        Route::get('delete-service/{service_id}', 'ServiceController@deleteService');

        // create new service request
        Route::post('new-service-request', 'RequestController@storeRequest');

        // update service request
        Route::post('update-service-request/{service_no}', 'RequestController@updateServiceRequest');

        // get service request
        Route::get('get-service-request/{service_no}', 'RequestController@getServiceRequest');

        // delete service request
        Route::get('delete-service-request/{service_no}', 'RequestController@deleteServiceRequest');

        // get all service request
        Route::get('get-all-service-requests', 'RequestController@getAllServiceRequests');

        // add rendered service to existing service request
        Route::post('add-rendered-service/{service_no}', 'RequestController@addRenderedService');

        // update rendered service
        Route::post('update-rendered-service', 'RequestController@updateRenderedService');

        // delete rendered service
        Route::get('delete-rendered-service/{rendered_service_id}', 'RequestController@deleteRenderedService');

        // add item to inventory
        Route::post('inventory/add-item', 'InventoryController@addItem');

        // get inventory item history
        Route::get('inventory/get-item-history/{item_number}', 'InventoryController@getItemHistory');

        // get all inventory items 
        Route::get('inventory/get-all-items', 'InventoryController@getAllInventoryItems');

        // edit item in inventory
        Route::post('inventory/edit-item', 'InventoryController@editItem');

        // add stock to inventory item
        Route::post('inventory/add-stock', 'InventoryController@addStock');

        // delete inventory item
        Route::get('inventory/delete-item/{item_number}', 'InventoryController@deleteItem');

        // add part to service request
        Route::post('request/add-part', 'PartController@addPartToRequest');
    });
   
    Route::get('/home', 'HomeController@index')->name('home');
});