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

class ServiceGroupController extends Controller
{
     /**
     * telling the class to inherit ApiResponse trait
     */
    use ApiResponse;    

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
            'service_group_avatar' => 'required|string|max:255',
            'service_group_name' => 'required|string|max:255',
        ]);


        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }

        $user = Auth::user();

        $serviceGroup = ServiceGroup::create([
            'staff_id' => $user->id,
            'service_group_avatar' => $request->service_group_avatar,
            'service_group_name' => ucfirst(strtolower($request->service_group_name)),
        ]);
 
        $message = "Service Group Created Successful!";

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
        $serviceGroup = $this->getServiceGroupById($service_group_id);
        
        $user = Auth::user();

        $request->service_group_name? $serviceGroup->staff_id = $user->id: null;
        $request->service_group_name? $serviceGroup->service_group_name = ucfirst(strtolower($request->service_group_name)): null;
        $request->service_group_name? $serviceGroup->service_group_avatar = $request->service_group_avatar: null;
        $serviceGroup->save();

        // // update user id on audit table
        // $this->auditRepository->updateUserId($request, $user);
        
        return $serviceGroup;
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
            $service_group['service_group_avatar'] = $serviceGroup->service_group_avatar; 
            $service_group['service_group_name'] = $serviceGroup->service_group_name;

            // get user details from users table
            $user = User::findOrFail($serviceGroup->staff_id);

            $service_group['staff_name'] = $user->full_name;
            $service_group['staff_avatar'] = $user->user_avatar;
            $service_group['updated_at'] = $serviceGroup->updated_at->format('d M Y');

            $service_group_detail[]=$service_group;
        }
        
        return $service_group_detail;
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
            $serviceGroup->delete();
            $message = "Service group and related services deleted successfully!";
        
        }else{
            // if user does not exist, display message
            $message = "Service group does not exist or has been deleted!";
        }

        return ['message' => $message];
    }
}
