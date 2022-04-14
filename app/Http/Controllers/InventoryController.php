<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\User;
use App\Models\Inventory;
use Carbon\Carbon;
use Validator;


class InventoryController extends Controller
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
    public function AddItem(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'item_name' => 'required|string|unique:inventories',
        ]);


        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }

        $inventory = new Inventory;
        // $is_admin = auth()->user()->is_admin();
        
        // created by and updated by user id
        $user = Auth::user();

        // Create new item
        $addItem = $inventory->create([
            'item_name' => $request->item_name,
            'cost_price' => $request->cost_price,
            'actual_price' => $request->actual_price,
            'quantity_added' => $request->quantity,
            'quantity_removed' => 0,
            'total_quantity' => $request->quantity,
            'reorder_level' => $request->reorder_level,
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id
        ]);

        return $addItem;
    }

     /**
     * Add stock to inventory item
     *
     * @param   object  $request object
     * @return  object
     */
    public function AddStock(Request $request)
    {

        $inventory = new Inventory;
        // $is_admin = auth()->user()->is_admin();
        
        $inventory_item = Inventory::where('item_name', $request->item_name)->orderBy('created_at', 'DESC')->first();

        // dd($inventory_item);
        
        // created by and updated by user id
        $user = Auth::user();

        // Create new item
        $addStock = $inventory->create([
            'item_name' => $inventory_item->item_name,
            'cost_price' => $inventory_item->cost_price,
            'actual_price' => $inventory_item->actual_price,
            'quantity_added' => $request->quantity,
            'quantity_removed' => 0,
            'total_quantity' => $inventory_item->total_quantity + $request->quantity,
            'reorder_level' => $inventory_item->reorder_level,
            'created_by_id' => $inventory_item->created_by_id,
            'updated_by_id' => $user->id
        ]);

        return $addStock;
    }

     /**
     * Edit item in inventory
     *
     * @param   object  $request object
     * @return  object
     */
    public function EditItem(Request $request)
    {
        $inventory_item = Inventory::where('id', $request->item_id)->orderBy('created_at', 'DESC')->first();

        // created by and updated by user id
        $user = Auth::user();

        // Create new item
        $inventory_item->update([
            'item_name' => $request->item_name,
            'cost_price' => $request->cost_price,
            'actual_price' => $request->actual_price,
            'quantity_added' => 0,
            'quantity_removed' => 0,
            'total_quantity' => $inventory_item->total_quantity,
            'reorder_level' => $request->reorder_level,
            'created_by_id' => $inventory_item->created_by_id,
            'updated_by_id' => $user->id
        ]);

        $message = "Inventory item editted successfully!";

        return $this->successResponse(["addStock" => $inventory_item], $message);
    }


}
