<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Traits\ApiResponse;
use App\Traits\FileUpload;
use Validator;

use Carbon\Carbon;


class VehicleController extends Controller
{
    /**
     * telling the class to inherit ApiResponse trait
     */
    use ApiResponse;  

    /**
     * telling the class to inherit FileUpload trait
     */
    use FileUpload;  
      

    /**
     * Get vehicle by using ID
     *
     * @param  int $user_id
     * @return array
     */
    public function getVehicleByVehicleId($vehicle_id)
    {
        return Vehicle::find($vehicle_id);
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
            'mileage' => $request->mileage,
            'full_name' => ucfirst(strtolower("$request->brand $request->model"))
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
        
        $request->brand? $vehicle->brand = ucfirst(strtolower($request->brand)): null;
        $request->model? $vehicle->model = ucfirst(strtolower($request->model)): null;
        $request->year? $vehicle->year = $request->year: null;
        $request->number? $vehicle->number = $request->number: null;
        $request->colour? $vehicle->colour = $request->colour: null;
        $request->mileage? $vehicle->mileage = $request->mileage: null;
        $request->brand? $vehicle->vehicle_name = "$request->brand $request->model": null;
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

    /**
     * upload vehicle avatar
     *
     * @param  \Illuminate\Http\Request $request $user_id
     * @return \App\Models\Vehicle
     */
    public function uploadAvatar(Request $request){
        
        // get user id and vehicle id of selected vehicle
        $user_id = $request->user_id;
        $vehicle_id = $request->vehicle_id;

        $validator = Validator::make($request->all(),[
                "image" => "required|file|mimes:jpeg,png,jpg,gif,svg|max:5048",
                "vehicle_id" => "required|integer"
            ]);

        if($validator->fails()){
        return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }

        // check if file to be uploaded exists
        if ($request->file()) {
            
            // get user records of selected user from users table
            $vehicle = $this->getVehicleByVehicleId($vehicle_id);
            
            // call image upload trait
            $newImageName = $this->newImageUpload($request, $vehicle);

            // save new image as user avatar
            $vehicle['avatar'] = $newImageName;

            $vehicle->save();

            $message = "Vehicle avatar uploaded successfully!";

        }else{
            $message = "Please select your avatar image";
        }
        
        return $this->successResponse(["Image" => $newImageName ], $message);
    }
    

    /**
     * delete vehicle avatar
     *
     * @param  string  $vehicle_id
     * @return \App\Models\User
     */
    public function deleteVehicleAvatar($vehicle_id)
    {
        // get vehicle details
        $vehicle = $this->getVehicleByVehicleId($vehicle_id);
        
        // check if vehicle details exist
        if($vehicle && $vehicle->avatar != 'vehicle.png'){   
            // delete uploaded image
            $message = $this->deleteUploadedImage($vehicle);
            $vehicle['avatar'] = 'vehicle.png';
            $vehicle->save();
            $message = "Avatar removed successfully!";   
        }else{
            // if user does not exist, display message
            $message = "Vehicle or vehicle avatar does not exist or has been deleted!";
        }

        return $this->successResponse([], $message);
    }



     /**
     * delete vehicle
     *
     * @param  string  $vehicle_id
     * @return \App\Models\Vehicle
     */
    public function deleteVehicle($vehicle_id)
    {
        // get vehicle details
        $vehicle = $this->getVehicleByVehicleId($vehicle_id);

        // check if vehicle details exist
        if($vehicle){
            // delete selected vehicle
            $vehicle->delete();
            $message = "Vehicle deleted successfully!";
        
        }else{
            // if vehicle does not exist, display message
            $message = "Vehicle does not exist or has been deleted!";
        }

        return ['message' => $message];
    }

}
