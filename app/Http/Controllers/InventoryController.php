<?php

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Traits\FileUpload;
use App\Models\ServiceGroup;
use App\Models\Service;
use App\Models\User;
use App\Models\Request as ServiceRequest;
use App\Models\RenderedService as RenderedService;
use Carbon\Carbon;
use Validator;


class InventoryController extends Controller
{

     /**
     * telling the class to inherit ApiResponse trait
     */
    use ApiResponse;   
    
      /**
     * Store ServiceRequest
     *
     * @param   object  $request object
     * @return  object
     */
    public function AddItem(Request $request)
    {

        // dd($request);

        $ServiceRequest = new ServiceRequest;
        // $is_admin = auth()->user()->is_admin();
        
        // created by and updated by user id
        $user = Auth::user();

        // Get Service
        // $service = Service::findOrFail($request->service);

        // Create Service Request and tracking no
        $uuid = str_replace('-', '', now()->toDateString()) . $this->genRandomNumber();
        $tracking = str_replace('-', '', now()->toDateString()) . mt_rand(11000, 99999);

        $newServiceRequest = $ServiceRequest->create([
            'user_id' => $request->user_id,
            'vehicle_id' => $request->vehicle_id,
            'service_no' => 'NA' . $uuid,
            'service_note' => $request->service_note,
            'tracking_no' => 'NAT' . $tracking,
            'is_done' => 0,
            'requester_id' => $user->id,
        ]);

        // dd($request);

        // Attach services to ServiceRequest
        // $newServiceRequest->services()->attach($service);

        // Attach status to ServiceRequest
        // $newServiceRequest->statuses()->attach(Status::where('request_id', $serviceRequest->id)->ServiceRequestBy('ServiceRequest')->first());

        // Create ServiceRequest waybills
        if(isset($request->rendered_services)) {
            foreach($request->rendered_services as $index => $rendSvc) {
                // dd($index, $user);
                $this->storeRenderedService($request, $newServiceRequest, $user, $index, $rendSvc);
            }
        }

        // if ($is_admin) {
        //     DBLogger::log("success", 'RequestCreated', "New request submitted for ". $user->email . " by " . auth()->user()->email, null, auth()->user()->email);
        // } else {
        //     DBLogger::log("success", 'RequestCreated', "New request submitted by ". $user->email, null, $user->email);
        // }

        // Broadcast Event
        // event(new RequestSubmitted($newServiceRequest));

        return $newServiceRequest;
    }

}
