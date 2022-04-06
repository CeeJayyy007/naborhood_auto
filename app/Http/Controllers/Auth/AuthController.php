<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Validator;
use App\Models\User;
use Auth;


class AuthController extends Controller
{
    /**
     * telling the class to inherit ApiResponse trait
     */
    use ApiResponse;    

    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(Request $request)
    {
        $msgs = [
            'name.required' => 'Name can not be empty!',
            'name.max' => 'Name can not be more than 255 characters!',
            'email.required_without' => 'Email address can not be empty!',
            'email.email' => 'Email address is invalid!',
            'email.max' => 'Email address can not be more than 255 characters!',
            'email.unique' => 'Email address already exists. Please, provide another!',
            'phone.unique' => 'Phone number already exists. Please, provide another!',
            'phone.required' => 'Phone number can not be empty!',
            'phone.numeric' => 'Phone number can be numbers only!',
            'phone.min' => 'Phone number can not be less than 11 characters!',
        ];    
        
        $validator = Validator::make($request->all(),[
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|numeric|min:11|unique:users',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:6',
        ]);


        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 200);
        }

        $user = User::create([
            'first_name' => ucfirst(strtolower($request->first_name)),
            'last_name' => ucfirst(strtolower($request->last_name)),
            'phone' => $request->phone,
            'email' => strtolower($request->email),
            'password' => bcrypt($request->password),
        ]);
 
        $token = $user->createToken('naborhood Token')->accessToken;

        $message = "Registration Successful!";

        return $this->successResponse(['user' => $user, 'token' => $token], $message);

    }
    

    public function login(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean'
        ]);

        if($validator->fails()){
            return $this->errorResponseWithDetails('Validation failed!', $validator->errors(), 200);
        }

        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials)){
            return $this->errorResponseWithDetails('Login Failed!', 'Incorrect email or password', 200);
        }

        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;

        if ($request->remember)
            $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        $message = "Login Successful!";

        return $this->successResponse(['user' => $user,  'token' => $tokenResult->accessToken,
        'type' => 'Bearer',
        'expiry' => Carbon::parse(
            $tokenResult->token->expires_at
        )->toDateTimeString()], $message);
    }

    public function logout(Request $request){
        $request->user()->token()->revoke();
        
        // return response()->json([
        //     'message' => 'Successfully logged out'
        // ]);
        
        $message = 'Successfully logged out';

        return $this->successResponse([], $message);
    }
}


