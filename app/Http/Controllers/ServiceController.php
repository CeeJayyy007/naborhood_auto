<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Traits\FileUpload;
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
     * telling the class to inherit FileUpload trait
     */
    use FileUpload;  

    /**
     * Get service by using ID
     *
     * @param  int $user_id
     * @return array
     */
    public function getServiceById($service_id)
    {
        return Service::find($service_id);
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
            'service_name' => 'required|string|unique:services,service_name,deleted_at|max:255',
            'service_price' => 'required|numeric',
            'image' => "file|mimes:jpeg,png,jpg,gif,svg|max:5048",
        ]);

        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }

        $checker = ServiceGroup::select('id')->where('id',$request->service_group_id)->exists();

        // check if selected service group exists
        if($checker){
            // check if file to be uploaded exists
            if ($request->file()) {    
                // call image upload trait
                $newImageName = $this->newCreatedImageUpload($request);
            }else{
                $newImageName = 'service.png';
            }

            // get id of staff creating new service group
            $user = Auth::user();

            // create service records
            $service = Service::create([
                'staff_id' => $user->id,
                'service_group_id' => $request->service_group_id,
                'service_name' => ucfirst(strtolower($request->service_name)),
                'avatar' => $newImageName,
                'service_price' => $request->service_price,
            ]);
    
            $message = "Service Created Successful!";
            return $this->successResponse(['service' => $service], $message);

        }else{
            // if selected service group does not exist, display message
            $message = "Selected service group does not exist or has been deleted!";
            return $this->successResponse([], $message);
        }
    }



    /**
     * edit service
     *
     * @param  \Illuminate\Http\Request $request $service_id
     * @return \App\Models\Service
     */
    public function editService(Request $request, $service_id)
    {
        $validator = Validator::make($request->all(),[
                "image" => "file|mimes:jpeg,png,jpg,gif,svg|max:5048",
            ]);
            
        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }

        $service = $this->getServiceById($service_id);

        // check if selected service group exists
        if($service){
            // check if file to be uploaded exists
            if ($request->file()) {    
                // call image upload trait
                $newImageName = $this->newImageUpload($request, $service); 
            }       
            
            $user = Auth::user();

            $request->service_name? $service->staff_id = $user->id: null;
            $request->service_group_id? $service->service_group_id = $service->service_group_id: null;
            $request->service_name? $service->service_name = ucfirst(strtolower($request->service_name)): null;
            $request->file()? $service->avatar = $newImageName: null;
            $request->service_price? $service->service_price = $request->service_price: null;
            $service->save();

            // // update user id on audit table
            // $this->auditRepository->updateUserId($request, $user);
            
            $message = "Service updated successfully!";
            return $this->successResponse(['service' => $service], $message);
        }else{
           // if selected service group does not exist, display message
            $message = "Selected service does not exist or has been deleted!";
            return $this->successResponse([], $message); 
        }
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
            $service['avatar'] = $service->avatar;
            $service['service_price'] = $service->service_price;

            // get user details from users table
            $user = User::findOrFail($service->staff_id);

            $service['staff_name'] = $user->full_name;
            $service['updated_at'] = $service->updated_at->format('d M Y');

            $service_detail[]=$service;
        }
        
        $message= "Service detail gotten successfully!";
        return $this->successResponse(['service_detail' => $service_detail], $message);
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

        $checker = ServiceGroup::select('id')->where('id',$service_group_id)->exists();

        // check if selected service group exists
        if($checker){
        
            $service_detail = [];

            foreach($services as $service){

                $service['service_group_id'] = $service->service_group_id; 
                $service['service_name'] = $service->service_name;
                $service['avatar'] = $service->avatar;
                $service['service_price'] = $service->service_price;

                // get user details from users table
                $user = User::findOrFail($service->staff_id);

                $service['staff_name'] = $user->full_name;
                $service['updated_at'] = $service->updated_at->format('d M Y');

                $service_detail[]=$service;
            }
        
            $message = "Service detail gotten successfully!";
            return $this->successResponse(['service_detail' => $service_detail], $message);
        }else{
           // if selected service group does not exist, display message
            $message = "Selected service group does not exist or has been deleted!";
            return $this->successResponse([], $message); 
        }
        
    }

     /**
     * delete service
     *
     * @param  string  $service_id
     * @return \App\Models\Service
     */
    public function deleteService($service_id)
    {
        // get service details
        $service = $this->getServiceById($service_id);

        // check if service details exist
        if($service){
            // delete selected service
            $this->deleteUploadedImage($service); 
            $service->delete();
            $message = "Service deleted successfully!";
        
        }else{
            // if service does not exist, display message
            $message = "Service does not exist or has been deleted!";
        }

       return $this->successResponse([], $message);
    }
}
