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
            $validator = Validator::make($request->all(),[
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|numeric|min:11|unique:users',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:6',
        ]);


        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 400);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
 
        $token = $user->createToken('naborhood Token')->accessToken;

        $message = "Registration Successful!";

        return $this->successResponse(['user' => $user, 'token' => $token], $message);

    }
    

    public function login(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return $this->errorResponseWithDetails('validation failed', $validator->errors(), 400);
        }

        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials)){
            return $this->errorResponseWithDetails('Invalid login credentials', $validator->errors(), 401);
        }

        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        $message = "Login Successful!";

        return $this->successResponse(['user' => $user,  'access_token' => $tokenResult->accessToken,
        'token_type' => 'Bearer',
        'expires_at' => Carbon::parse(
            $tokenResult->token->expires_at
        )->toDateTimeString()], $message);
    }

    public function logout(Request $request){
        $request->user()->token()->revoke();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
        
    }
}


