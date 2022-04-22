<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Models\Request as ServiceRequest;
use App\Models\RenderedService as RenderedService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\User;
use App\Models\Part;
use App\Models\Inventory;
use Carbon\Carbon;
use Validator;

class PartController extends Controller
{
     /**
     * telling the class to inherit ApiResponse trait
     */
    use ApiResponse;   
    
      /**
     * Add item to inventory
     *
     * @param   object  $request object
     * @return  object
     */
    public function addPartToRequest(Request $request)
    {
        $part = new Part;
        
        // created by and updated by user id
        $user = Auth::user();

        // get inventory item detail from inventory table
        $item = Inventory::find($request->inventory_item_id);

        // Create new item
        $added_part = $part->create([
            'rendered_service_id' => $request->rendered_service_id,
            'inventory_item_id' =>  $request->inventory_item_id,
            'price' => $item->actual_price,
            'quantity' => $request->quantity,
            'amount' => $request->quantity * $item->actual_price,
        ]);

        $rendered_service = RenderedService::find($request->rendered_service_id);

        $rendered_service->update([
            'updated_by_id' => $user->id,
        ]);

        $message = "Part(s) added to service request successfully!";

        return $this->successResponse([$added_part, $rendered_service], $message);
    }
}
