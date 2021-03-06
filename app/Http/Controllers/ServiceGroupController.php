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

class ServiceGroupController extends Controller
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
     * Get service group by using ID
     *
     * @param  int $user_id
     * @return array
     */
    public function getServiceGroupById($service_group_id)
    {
        return ServiceGroup::find($service_group_id);
        // return User::withTrashed()->findOrFail($user_id);
    }
    
    
    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function newServiceGroup(Request $request)
    {   
        $validator = Validator::make($request->all(),[
            'service_group_name' => 'required|string|unique:service_groups,service_group_name,deleted_at|max:255',
            'image' => "file|mimes:jpeg,png,jpg,gif,svg|max:5048",
        ]);

        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }
        
        // check if file to be uploaded exists
        if ($request->file()) {    
            // call image upload trait
            $newImageName = $this->newCreatedImageUpload($request);
        }else{
            $newImageName = 'service_group.png';
        }
        
        // get id of staff creating new service group
        $user = Auth::user();

        $serviceGroup = ServiceGroup::create([
            'staff_id' => $user->id,
            'service_group_name' => ucfirst(strtolower($request->service_group_name)),
            'avatar' => $newImageName,
        ]);
 
        $message = "Service Group Created Successfully!";

        return $this->successResponse(['serviceGroup' => $serviceGroup], $message);
    }



    /**
     * edit service group
     *
     * @param  \Illuminate\Http\Request $request $service_group_id
     * @return \App\Models\ServiceGroup
     */
    public function editServiceGroup(Request $request, $service_group_id)
    {
        $validator = Validator::make($request->all(),[
                "image" => "file|mimes:jpeg,png,jpg,gif,svg|max:5048",
            ]);
            
        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }

        $service_group = $this->getServiceGroupById($service_group_id);
        
        // check if file to be uploaded exists
        if ($request->file()) {    
            // call image upload trait
            $newImageName = $this->newImageUpload($request, $service_group); 
        }
        
        $user = Auth::user();

        if($service_group){           
            $request->service_group_name? $service_group->staff_id = $user->id: null;
            $request->service_group_name? $service_group->service_group_name = ucfirst(strtolower($request->service_group_name)): null;
            $request->file()? $service_group->avatar = $newImageName: null;
            $service_group->save();
            $message = "Service group updated successfully!";
        }else{
            $message = "Selected user does not exist or has been deleted!";
            return $this->successResponse([], $message);
        }
            
        // // update user id on audit table
        // $this->auditRepository->updateUserId($request, $user);
        
        return $this->successResponse(['service_group' => $service_group], $message);
    }

    
     /**
     * get all user service group details
     *
     * @return \App\Models\ServiceGroup
     */
    public function getServiceGroup()
    {
        $serviceGroups = ServiceGroup::all();

        $service_group_detail = [];

        foreach($serviceGroups as $serviceGroup){
            $service_group['id'] = $serviceGroup->id; 
            $service_group['avatar'] = $serviceGroup->avatar; 
            $service_group['service_group_name'] = $serviceGroup->service_group_name;

            // get user details from users table
            $user = User::findOrFail($serviceGroup->staff_id);

            $service_group['staff_name'] = $user->full_name;
            $service_group['staff_avatar'] = $user->avatar;
            $service_group['updated_at'] = $serviceGroup->updated_at->format('d M Y');

            $service_group_detail[]=$service_group;
            
        }
        $message= "Service detail gotten successfully!";
        return $this->successResponse(['service_group_detail' => $service_group_detail], $message);
    }

    /**
     * delete service group
     *
     * @param  string  $service_group_id
     * @return \App\Models\ServiceGroup
     */
    public function deleteServiceGroup($service_group_id)
    {
        // get service group details
        $serviceGroup = $this->getServiceGroupById($service_group_id);

        // check if service group details exist
        if($serviceGroup){
            // get services belonging to selected user
            $services = Service::where('service_group_id', $serviceGroup->id)->get();
            
            // loop through service group data incase user has multiple services
            foreach($services as $service){
                $service->delete();                
            } 
            // delete selected service group
            $this->deleteUploadedImage($serviceGroup); 
            $serviceGroup->delete();
            $message = "Service group and related services deleted successfully!";
        
        }else{
            // if user does not exist, display message
            $message = "Service group does not exist or has been deleted!";
        }

        return $this->successResponse([], $message);
    }
}
