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
        $serviceRequest = Request::where('service_no', $service_no)->first();
        $user = User::findOrFail($request->user_id);

        $ServiceRequest->update([
            'vehicle_id' => $request->vehicle_id,
            'service_note' => $request->service_note,
            'is_done' => $request->is_done,
        ]);

        // Create ServiceRequest waybills
        if(isset($request->rendered_services)) {
            foreach($request->rendered_services as $index => $rendSvc) {
                $this->storeRenderedService($request, $newServiceRequest, $user, $index, $rendSvc);
            }
        }
        
        // Broadcast Event
        // event(new RequestServiceRequestUpdated($serviceRequest));

        return $serviceRequest;
    }
    
      /**
     * Update rendered service
     *
     * @param   object  $request object
     * @param   object  $serviceRequest App\Request object
     * @return  object  rendered service object
     */
    public function updateRenderedService(Request $request, $rendered_service_id)
    {
        // updated by user id
        $user = Auth::user();

        // get details of previously rendered service
        $rendSvc = RenderedService::findOrFail('$rendered_service_id')->first();

        // update previously rendered service
        $rendSvc->update([
            "service_id" => $reqeust->service_id,
            "service_group_id" => $request->service_group_id, 
            "price" => $request->price,
            "quantity" => $request->quantity,
            "total" => $request->total,
            "status" => $request->status,
            "updated_by_id" => $user->id, 
        ]);

        // Update waybill statuses
        // $wyb->statuses()->attach(Status::where('service_type', $service->type)->ServiceRequestBy('ServiceRequest')->first(), ['request_id' => $ServiceRequest->id]);

        return $rendSvc;
    }

    /**
     * delete a rendered service, rendered service statuses, rendered seervice invoices
     *
     * @param   array $rendered_service_ids
     * @return  bool
     */
    public function deleteRenderedService($rendered_service_id)
    {
        $rendSvc = RenderedService::findOrFail('rendered_service_id')->first();

        $rendSvc->delete();

        // Waybill::whereIn('waybill_number', $wyb_nums)
        //             ->update([
        //                 'tracking_id' => null,
        //                 'is_valid' => 0,
        //                 'is_received' => 0,
        //                 'is_received_date' => null,
        //                 'is_done' => 0,
        //             ]);

            // DB::table('waybill_statuses')->whereIn('waybill_id', $waybill_ids)->delete();
            // DB::table('waybill_invoices')->whereIn('waybill_id', $waybill_ids)->delete();
    }

    /**
     * Cancel serviceRequest$serviceRequest, detach all relationships (services, waybills, statuses, invoices)
     *
     * @param   int $order_id
     * @return  void
     */
    public function cancelServiceRequest($request_id)
    {
        $serviceRequest = Request::where('id', $request_id);

        $renderedServices = RenderedService::where('request_id', $request_id);

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
