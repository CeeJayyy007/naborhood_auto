<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\ServiceGroup;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Validator;

class ServiceController extends Controller
{
     /**
     * telling the class to inherit ApiResponse trait
     */
    use ApiResponse;    

    /**
     * Get service by using ID
     *
     * @param  int $user_id
     * @return array
     */
    public function getServiceById($service_id)
    {
        return Service::findOrFail($service_id);
        // return User::withTrashed()->findOrFail($user_id);
    }
    
    
    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function newService(Request $request)
    {   
        $validator = Validator::make($request->all(),[
            'service_group_id' => 'required|numeric',
            'service_name' => 'required|string|max:255',
            'service_avatar' => 'required|string|max:255',
            'service_price' => 'required|numeric',
        ]);


        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }

        $user = Auth::user();

        $service = Service::create([
            'staff_id' => $user->id,
            'service_group_id' => $request->service_group_id,
            'service_name' => ucfirst(strtolower($request->service_name)),
            'service_avatar' => $request->service_avatar,
            'service_price' => $request->service_price,
        ]);
 
        $message = "Service Created Successful!";

        return $this->successResponse(['service' => $service], $message);
    }



    /**
     * edit service
     *
     * @param  \Illuminate\Http\Request $request $service_id
     * @return \App\Models\Service
     */
    public function editService(Request $request, $service_id)
    {
        $service = $this->getServiceById($service_id);
        
        $user = Auth::user();

        $request->service_name? $service->staff_id = $user->id: null;
        $request->service_name? $service->service_group_id = $service->service_group_id: null;
        $request->service_name? $service->service_name = ucfirst(strtolower($request->service_name)): null;
        $request->service_avatar? $service->service_avatar = $request->service_avatar: null;
        $request->service_price? $service->service_price = $request->service_price: null;
        $service->save();

        // // update user id on audit table
        // $this->auditRepository->updateUserId($request, $user);
        
        return $service;
    }


     /**
     * get all user service details
     *
     * @param  string  $user_id
     * @return \App\Models\Service
     */
    public function getAllServices()
    {
        $services = Service::all();

        foreach($services as $service){
            // $service['id'] = $service->id; 
            $service['service_group_id'] = $service->service_group_id; 
            $service['service_name'] = $service->service_name;
            $service['service_avatar'] = $service->service_avatar;
            $service['service_price'] = $service->service_price;

            // get user details from users table
            $user = User::findOrFail($service->staff_id);

            $service['staff_name'] = $user->full_name;
            $service['updated_at'] = $service->updated_at->format('d M Y');

            $service_detail[]=$service;
        }
        
        return $service_detail;
    }

     /**
     * get all service details for selected service group
     *
     * @param  string  $service_group_id
     * @return \App\Models\Service
     */
    public function getService($service_group_id)
    {
        $services = Service::where('service_group_id', $service_group_id)->get();
        
        $service_detail = [];
        
        foreach($services as $service){

            $service['service_group_id'] = $service->service_group_id; 
            $service['service_name'] = $service->service_name;
            $service['service_avatar'] = $service->service_avatar;
            $service['service_price'] = $service->service_price;

            // get user details from users table
            $user = User::findOrFail($service->staff_id);

            $service['staff_name'] = $user->full_name;
            $service['updated_at'] = $service->updated_at->format('d M Y');

            $service_detail[]=$service;
        }
        
        return $service_detail;
    }
}
