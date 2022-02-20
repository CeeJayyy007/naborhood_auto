<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Traits\ApiResponse;
use Validator;

use Carbon\Carbon;


class VehicleController extends Controller
{
     /**
     * telling the class to inherit ApiResponse trait
     */
    use ApiResponse;    

    /**
     * Get vehicle by using ID
     *
     * @param  int $user_id
     * @return array
     */
    public function getVehicleByUserId($user_id)
    {
        return Vehicle::findOrFail($user_id);
        // return User::withTrashed()->findOrFail($user_id);
    }
    
    
    /**
     * Get Users using their ID
     *
     * @param  array $user_ids
     * @return array
     */
    public function getVehiclesbyUsersIds($user_ids)
    {
        return Vehicle::findMany($user_ids);
    }

    /**
     * pluck users ids from request then get model instances from db
     *
     * @param  Request $request
     * @return array
     */
    public function extractIdsAndFetchVehicles($request)
    {
    	$vehicle_ids = [];

    	foreach ($request->vehicles as $vehicle) {
    		array_push($vehicle_ids, $vehicle['vehicle_id']);
    	}

        return $this->getVehiclesByUserIds($user_ids);
    }

        /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function registerVehicle(Request $request)
    {   
        $validator = Validator::make($request->all(),[
            'user_id' => 'required|numeric',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'numeric',
            'number' => 'required|string',
            'colour' => 'required|string',
            'mileage' => 'numeric',
        ]);


        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }

        $vehicle = Vehicle::create([
            'user_id' => $request->user_id,
            'brand' => ucfirst(strtolower($request->brand)),
            'model' => ucfirst(strtolower($request->model)),
            'year' => $request->year,
            'number' => strtoupper($request->number),
            'colour' => ucfirst(strtolower($request->colour)),
            'mileage' => $request->mileage
        ]);
 
        $message = "Vehicle Registration Successful!";

        return $this->successResponse(['vehicle' => $vehicle], $message);

    }



    /**
     * edit vehicle profile
     *
     * @param  \Illuminate\Http\Request $request $user_id
     * @return \App\Models\Vehicle
     */
    public function editVehicleProfile(Request $request, $user_id)
    {
        $vehicle = $this->getVehicleByUserId($user_id);
        
        $request->brand? $vehicle->brand = $request->brand: null;
        $request->model? $vehicle->model = $request->model: null;
        $request->year? $vehicle->year = $request->year: null;
        $request->number? $vehicle->number = $request->number: null;
        $request->colour? $vehicle->colour = $request->colour: null;
        $request->mileage? $vehicle->mileage = $request->mileage: null;
        $vehicle->save();

        // // update user id on audit table
        // $this->auditRepository->updateUserId($request, $user);
        
        return $vehicle;
    }

    /**
     * get vehicle profile
     *
     * @param  string  $user_id
     * @return \App\Models\Vehicle
     */
    public function getVehicleProfile($user_id)
    {
        $vehicle = $this->getUserById($user_id);
        
        return $vehicle;
    }

     /**
     * get all user vehicle details
     *
     * @param  string  $user_id
     * @return \App\Models\Vehicle
     */
    public function getAllUserVehicleDetail()
    {
        $users = Vehicle::all();

        foreach($users as $user){
            $user_detail['id'] = $user->id; 
            $user_detail['full_name'] = $user->full_name; 
            $user_detail['phone'] = $user->phone;
            $user_detail['email'] = $user->email;
            $user_detail['role'] = $user->role;

            $users_details[]=$user_detail;
        }
        
        return $users_details;
    }
}