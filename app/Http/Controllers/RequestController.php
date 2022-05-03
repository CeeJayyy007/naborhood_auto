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
     * @param   int  $service_no
     * @return  object
     */
    public function updateServiceRequest(Request $request, $service_no)
    {
        $serviceRequest = ServiceRequest::where('service_no', $service_no)->first();
        $user = User::findOrFail($request->user_id);

        $serviceRequest->update([
            'vehicle_id' => $request->vehicle_id,
            'service_note' => $request->service_note,
            'is_done' => $request->is_done,
        ]);

        // Create ServiceRequest waybills
        if(isset($request->rendered_services)) {
            foreach($request->rendered_services as $index => $rendSvc) {
                $this->storeRenderedService($request, $serviceRequest, $user, $index, $rendSvc);
            }
        }
        
        // Broadcast Event
        // event(new RequestServiceRequestUpdated($serviceRequest));

        return $serviceRequest;
    }
    
    /**
     * add new rendered service to service request
     *
     * @param   object  $request object
     * @param   object  $serviceRequest App\Request object
     * @return  object  rendered service object
     */
    public function addRenderedService(Request $request, $service_no)
    {
        // updated by user id
        $user = Auth::user();

        // get details from existing service request
        $serviceRequest = ServiceRequest::where('service_no', $service_no)->first();

        // get newwly rendered_services array
        $rendered_services = $request->rendered_services;
        
        // create records of each rendered services in the array
        foreach ($rendered_services as $rendered_service){
            // dd($rendered_service['service_id']);
            
            // create and add new rendered service to previously created service request
            $rendSvc = RenderedService::create([
                "request_id" => $serviceRequest->id,
                "service_id" => $rendered_service['service_id'],
                "service_group_id" => $rendered_service['service_group_id'], 
                "price" => $rendered_service['price'],
                "quantity" => $rendered_service['quantity'],
                "total" => $rendered_service['price'] * $rendered_service['quantity'],
                "status" => 0,
                "updated_by_id" => $user->id, 
                "created_by_id" => $user->id,
            ]);
            
        }
        // Update waybill statuses
        // $wyb->statuses()->attach(Status::where('service_type', $service->type)->ServiceRequestBy('ServiceRequest')->first(), ['request_id' => $ServiceRequest->id]);

        return $rendSvc;
    }


      /**
     * Update rendered service
     *
     * @param   object  $request object
     * @param   object  $serviceRequest App\Request object
     * @return  object  rendered service object
     */
    public function updateRenderedService(Request $request)
    {
        // updated by user id
        $user = Auth::user();

        // get details of previously rendered service
        $rendSvc = RenderedService::findOrFail($request->rendered_service_id);

        // update previously rendered service
        $rendSvc->update([
            "service_id" => $request->service_id,
            "service_group_id" => $request->service_group_id, 
            "price" => $request->price,
            "quantity" => $request->quantity,
            "total" => $request->price * $request->quantity,
            "status" => $request->status,
            "updated_by_id" => $user->id, 
        ]);

        // Update waybill statuses
        // $wyb->statuses()->attach(Status::where('service_type', $service->type)->ServiceRequestBy('ServiceRequest')->first(), ['request_id' => $ServiceRequest->id]);

        return $rendSvc;
    }

     /**
     * get service request details
     *
     * @return \App\Models\Request
     */
    public function getServiceRequest($service_no)
    {
        // get service request for selected service_no
        $serviceRequest = ServiceRequest::where('service_no', $service_no)->first();

        // get rendered services for selected service request
        $renderedServices = RenderedService::where('request_id', $serviceRequest->id)->get();

        // create new array to hold service request detail
        $service_request_detail = [];

        // populate array
        $service_request_detail['service_request'] = $serviceRequest; 
        $service_request_detail['rendered_services'] = $renderedServices;

        // create success message
        $message= "Service request and all rendered services obtained successfully!";
        
        return $this->successResponse(['service_request_detail' => $service_request_detail], $message);
    }


    /**
     * get all service request details
     *
     * @return \App\Models\Request
     */
    public function getAllServiceRequests()
    {
        // get service request for selected service_no
        $serviceRequests = ServiceRequest::all();

        return $serviceRequests;

        // create new array to hold service request detail
        $service_request_detail = [];

        foreach ($serviceRequests as $serviceRequest){
            // get rendered services for selected service request
            $renderedServices = RenderedService::where('request_id', $serviceRequest->id)->get();

            // populate array
            $service_request_detail['service_request'] = $serviceRequest; 
            $service_request_detail['rendered_services'] = $renderedServices;
        }

        // create success message
        $message= "All service requests and their rendered services obtained successfully!";
        
        return $this->successResponse(['service_request_detail' => $service_request_detail], $message);
    }


    /**
     * delete a rendered service, rendered service statuses, rendered seervice invoices
     *
     * @param   array $rendered_service_ids
     * @return  bool
     */
    public function deleteRenderedService($rendered_service_id)
    {
        $rendSvc = RenderedService::findOrFail($rendered_service_id);

        $rendSvc->delete();

        $message = "Selected rendered service deleted successfully!";
        
        return $this->successResponse([], $message);
    }

    /**
     * Cancel serviceRequest$serviceRequest, detach all relationships (services, waybills, statuses, invoices)
     *
     * @param   int $order_id
     * @return  void
     */
    public function deleteServiceRequest($service_no)
    {
        $serviceRequest = ServiceRequest::where('service_no', $service_no)->first();

        $renderedServices = RenderedService::where('request_id', $serviceRequest->id)->get();

        foreach($renderedServices as $renderedService){
            
            $renderedService->delete();
        
        }

        $serviceRequest->delete();

        $message = "Service Request deleted successfully!";

        // if($is_user) {
        //     $serviceRequest= $serviceRequest->where('user_id', auth()->user()->id)->first();
        // } else {
        //     $serviceRequest= $serviceRequest->first();
        // }


        // if (!$serviceRequest) {
        //     return null;
        // }

        // $serviceRequest->invoices()->delete();
        // $serviceRequest->statuses()->detach();
        // DB::table('waybill_invoices')->whereIn('waybill_id', $serviceRequest->waybills()->pluck('id')->toArray())->delete();
        // DB::table('waybill_statuses')->whereIn('waybill_id', $serviceRequest->waybills()->pluck('id')->toArray())->delete();
        // $serviceRequest->renderedService()->delete();

        // Broadcast event
        // event(new OrderCancelled($serviceRequest));

        return $this->successResponse([], $message);
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
