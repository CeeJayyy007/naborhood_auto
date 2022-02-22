<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;


class UserController extends Controller
{
    
    /**
    * telling the class to inherit ApiResponse trait
    */
    use ApiResponse;    

    /**
     * Get User using ID
     *
     * @param  int $user_id
     * @return array
     */
    public function getUserById($user_id)
    {
        return User::findOrFail($user_id);
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
        
        $request->first_name? $user->first_name = $request->first_name: null;
        $request->last_name? $user->last_name = $request->last_name: null;
        $request->dob? $user->dob = $request->dob: null;
        $request->dob? $user->age = Carbon::parse($request->dob)->age: null;
        $request->gender? $user->gender = $request->gender: null;
        $request->phone? $user->phone = $request->phone: null;
        $request->email? $user->email = $request->email: null;
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
        $users = User::all();

        foreach($users as $user){
            $user_detail['id'] = $user->id; 
            $user_detail['full_name'] = $user->full_name; 
            $user_detail['phone'] = $user->phone;
            $user_detail['email'] = $user->email;
            $user_detail['role'] = $user->role;

            $vehicle = Vehicle::where('user_id', $user->id)
                                ->get();

            $user_detail['vehicle'] = $vehicle;
            
            $users_details[] = $user_detail;
        }
        
        // $message = "User details created successfully!";

        // return $this->successResponse(['users_details' => $users_details], $message);

        return $users_details;
    }
}
