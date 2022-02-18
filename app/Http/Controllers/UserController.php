<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;



class UserController extends Controller
{
    
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
     * edit patient profile
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Models\User
     */
    public function editProfile(Request $request)
    {
        $user = $this->getUserById($request->user_id);
        
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
}
