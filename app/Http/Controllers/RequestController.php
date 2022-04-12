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
        // $wyb->statuses()->attach(Status::where('service_type', $service->type)->ServiceRequestBy('ServiceRequest')->first(), ['request_id' => $ServiceRequest->id]);

        return $rendSvc;
    }


    /**
     * Update ServiceRequest
     *
     * @param   object  $request object
     * @param   int  $ServiceRequest_no
     * @return  object
     */
    public function updateServiceRequest(Request $request, $service_no)
    {
        $ServiceRequest = Request::where('service_no', $service_no)->first();
        $user = User::findOrFail($request->user_id);

        $ServiceRequest->update([
            'title' => $request->title,
            'comment' => $request->extra_comment,
            'send_address_id' => $send_address_id,
            // 'receive_address_id' => $address ?? $request->o_address,
            'receive_address_id' => $receive_address_id,
            'tracking_number' => null,
            'distance' => $distance,
        ]);

        // if service changes, detach old services and attach new services
        if ($ServiceRequest->services()->first()->id != $request->service) {
            $ServiceRequest->services()->detach();

            $service = Service::findOrFail($request->service);
            $relatedService = $service->relatedService()->first();

            $ServiceRequest->services()->attach($service);
            $ServiceRequest->services()->attach($relatedService);
            if (isset($local_request)) {
                $ServiceRequest->services()->attach($local_request);
            }
        }
        else {
            $service = $ServiceRequest->services->first();
            $relatedService = $service->relatedService()->first();
        }

        if($request->submit == "local-ServiceRequest"){
            $request->waybill_number = array("HGL001");
            $request->courier_name = array("HUGO");
        }

        // cancel marked waybills
        if (isset($request->w_delete)) {
            $waybill_ids = array_values($request->w_delete);
            $this->invalidateWaybill($waybill_ids);
        }

        // update old waybills
        if ($request->submit == "china-ServiceRequest") {
            $this->updateWaybills($request, $ServiceRequest, $service, $relatedService, $local_request);
        }

        // create ServiceRequest waybills
        if(isset($request->waybill_number)) {
            foreach($request->waybill_number as $index => $wyb) {
                $this->storeWaybill($request, $ServiceRequest, $service, $relatedService, $local_request, $index);
            }
        }

        // Broadcast Event
        event(new RequestServiceRequestUpdated($ServiceRequest));

        return $ServiceRequest;
    }
    
    /**
     * Generate random number for ServiceRequest_no
     *
     * @return integer
     */
    private function genRandomNumber()
    {
        mt_srand(crc32(microtime()));
        return mt_rand(10000, 99999);
    }
}
