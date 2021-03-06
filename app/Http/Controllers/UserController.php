<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Traits\FileUpload;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Validator;

class UserController extends Controller
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
     * Get User using ID
     *
     * @param  int $user_id
     * @return array
     */
    public function getUserById($user_id)
    {
        return User::find($user_id);
        // return User::withTrashed()->findOrFail($user_id);
    }
    
    
    /**
     * attach roles to user
     *
     * @param  int  $user_id
     * @param  array  $roles
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function addUserRoles($user_id, $roles, $request)
    {
        // get user instance
        $user = $this->getUserById($user_id);

        // attach roles if any
        $request->filled('roles')? $user->assignRole($roles): null;   
    }


    /**
     * Get Users using their ID
     *
     * @param  array $user_ids
     * @return array
     */
    public function getUsersByIds($user_ids)
    {
        return User::findMany($user_ids);
    }

    /**
     * pluck users ids from request then get model instances from db
     *
     * @param  Request $request
     * @return array
     */
    public function extractIdsAndFetchUsers($request)
    {
    	$user_ids = [];

    	foreach ($request->users as $user) {
    		array_push($user_ids, $user['user_id']);
    	}

        return $this->getUsersByIds($user_ids);
    }

    /**
     * Get User using token
     *
     * @param  int $user_id
     * @return array
     */
    public function getUserByToken(Request $request)
    {
        return $request->user();
    }

    /**
     * Get Users by filters
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Models\User
     */
    public function getUsers(Request $request)
    {
        $result = User::filter($request->all())->get()->all();
        
        return $result;
    }

    /**
     * edit user profile
     *
     * @param  \Illuminate\Http\Request $request $user_id
     * @return \App\Models\User
     */
    public function editProfile(Request $request, $user_id)
    {
        $user = $this->getUserById($user_id);
        
        $request->first_name? $user->first_name = ucfirst(strtolower($request->first_name)): null;
        $request->last_name? $user->last_name = ucfirst(strtolower($request->last_name)): null;
        $request->dob? $user->dob = $request->dob: null;
        $request->dob? $user->age = Carbon::parse($request->dob)->age: null;
        $request->gender? $user->gender = $request->gender: null;
        $request->phone? $user->phone = $request->phone: null;
        $request->email? $user->email = $request->email: null;
        $request->first_name? $user->full_name = "$request->first_name $request->last_name": null;
        $user->save();

        // add roles
        if ($request->filled('roles')) {
            $roles = $request->roles;
            $roles_with_branch_id = [];

            foreach ($roles as $role_name) {
                // append branch_id to the role_name
                $role_name = $role_name.'_'.$request->branch_id;
                array_push($roles_with_branch_id, $role_name);
            }

            $user->syncRoles($roles_with_branch_id);
        }

        // // update user id on audit table
        // $this->auditRepository->updateUserId($request, $user);
        
        return $user;
    }

    /**
     * get user profile
     *
     * @param  string  $user_id
     * @return \App\Models\User
     */
    public function getProfile($user_id)
    {
        $user = $this->getUserById($user_id);
        
        return $user;
    }

     /**
     * get all user details
     *
     * @param  string  $user_id
     * @return \App\Models\User
     */
    public function getAllUserDetail()
    {
        // get all users 
        $users = User::all();

        // initialize users_details array
        $users_details = [];

        // populate user_detail array
        foreach($users as $user){
            $user_detail['id'] = $user->id; 
            $user_detail['full_name'] = $user->full_name; 
            $user_detail['phone'] = $user->phone;
            $user_detail['email'] = $user->email;
            $user_detail['role'] = $user->role;

            // get vehicle details for selected user
            $vehicle = Vehicle::where('user_id', $user->id)
                                ->get();

            $user_detail['vehicle'] = $vehicle;
            
            $users_details[] = $user_detail;
        }
        
        // $message = "User details created successfully!";

        // return $this->successResponse(['users_details' => $users_details], $message);

        return $users_details;
    }


     /**
     * upload user avatar
     *
     * @param  \Illuminate\Http\Request $request $user_id
     * @return \App\Models\User
     */
    public function uploadAvatar(Request $request){

        // get user id of selected user
        $user_id = $request->user_id;
        
        // validate inputs
        $validator = Validator::make($request->all(),[
            "image" => "required|file|mimes:jpeg,png,jpg,gif,svg|max:5048",
            "user_id" => "required|integer"
        ]);
        
        // check validator
        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }
        
        // check if file to be uploaded exists
        if ($request->file()) {
            
            // get user records of selected user from users table
            $user = $this->getUserById($user_id);
            
            // call image upload trait
            $newImageName = $this->newImageUpload($request, $user);

            // save new image as user avatar
            $user['avatar'] = $newImageName;

            $user->save();

            $message = "User avatar uploaded successfully!";

        }else{
            $message = "Please select your avatar image";
        }
        
        return $this->successResponse(["Image" => $newImageName ], $message);
    }
    

    /**
     * delete user avatar
     *
     * @param  string  $user_id
     * @return \App\Models\User
     */
    public function deleteUserAvatar($user_id)
    {
        // get user details
        $user = $this->getUserById($user_id);
        
        // check if user details exist
        if($user && $user->avatar != null){   
            // delete uploaded image
            $message = $this->deleteUploadedImage($user);
            
        }else{
            // if user does not exist, display message
            $message = "User or user avatar does not exist or has been deleted!";
        }

        return $this->successResponse([], $message);
    }


    
     /**
     * delete user
     *
     * @param  string  $user_id
     * @return \App\Models\User
     */
    public function deleteUser($user_id)
    {
        // get user details
        $user = $this->getUserById($user_id);

        // check if user details exist
        if($user){
            // get vehicles belonging to selected user
            $vehicles = Vehicle::where('user_id', $user->id)->get();

            // loop through vehicle data incase user has multiple vehicles
            foreach($vehicles as $vehicle){
                $vehicle->delete();                
            } 
            // delete selected user
            $user->delete();
            $message = "User and related vehicles deleted successfully!";
        
        }else{
            // if user does not exist, display message
            $message = "User does not exist or has been deleted!";
        }

        return $this->successResponse([], $message);
    }


}
