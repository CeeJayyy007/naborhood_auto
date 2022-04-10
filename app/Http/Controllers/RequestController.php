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
use App\Models\Request as ServiceRequest;
use App\Models\RenderedService as RenderedService;
use Carbon\Carbon;
use Validator;

class RequestController extends Controller
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
    public function storeRequest(Request $request)
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
        // $newServiceRequest->statuses()->attach(Status::where('request_id', $serviceRequest->id)->OrderBy('ServiceRequest')->first());

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

    /**
     * Store rendered service
     *
     * @param   object  $request object
     * @param   object  $serviceRequest App\Request object
     * @return  object  renderedSerice object
     */
    public function storeRenderedService($request, $newServiceRequest, $user, $index = null, $rendSvc)
    {

        // dd($request->rendered_services);

        $rendSvc = RenderedService::create([
            "request_id" => $newServiceRequest->id,
            "service_id" => $rendSvc['service_id'],
            "service_group_id" => $rendSvc['service_group_id'], 
            "price" => $rendSvc['price'],
            "quantity" => $rendSvc['quantity'],
            "total" => $rendSvc['total'],
            "status" => 0,
            "created_by_id" => $user->id,
            "updated_by_id" => $user->id, 
        ]);

        // Update waybill statuses
        // $wyb->statuses()->attach(Status::where('service_type', $service->type)->orderBy('order')->first(), ['request_id' => $order->id]);

        return $rendSvc;
    }
    
    /**
     * Generate random number for order_no
     *
     * @return integer
     */
    private function genRandomNumber()
    {
        mt_srand(crc32(microtime()));
        return mt_rand(10000, 99999);
    }
}
