<?php

namespace App\Http\Controllers;

use App\Mail\Testmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Input\Input;
use JWTAuth;

class UserController extends Controller{

    public function register(Request $request)  {
        $data = $request->only('name','address','phone','email','password');
        $vadidator = Validator::make($data,[
            "name" =>  "required|max:255|unique:users",
            "icon" =>  "image|mimes:jpeg,png,jpg",
            "address" => "required|max:255",
            "phone" => "required|max:11",
            "email" => "required|email|max:255|unique:users",
            "password" => 'required|string|min:8|max:50',
        ]);
        if($vadidator->fails()) {
            return response()->json(["error" => $vadidator->errors()->toJson()],200);
        }
        $user = User::create([
            "name" => $request->name,
            "icon" =>$request->icon,
            "address" => $request->address,
            "phone" => $request->phone,
            "email" => $request->email,
            'password' => bcrypt($request->password),
        ]);
        return response()->json([
            "mess" => "User created successfully",
            "user" => $user,
            "Token" => JWTAuth::attempt($data),
        ]);
    }
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');
        $vadidator = Validator::make($credentials,[
            'email' => 'required|email',
            'password' => 'required|string|min:8|max:50'

        ]);
        if ($vadidator->fails()) {
            return response()->json(['error' => $vadidator->errors()->toJson()], 200);
        }
        try {
            if (!$token = JWTAuth::attempt($credentials))  {
                return response()->json([
                	'success' => false,
                	'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
    	return $credentials;
        }
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }
    public function show($id)  {
        $user = User::find($id);
        if(!$user)  {
            return response()->json([
                "user" => "Null",
            ]);
        }
        return response()->json([
            "user" => $user,
        ]);
    }
    public function showAll()  {
        $user = User::all();
        return response()->json([
            "user" => $user,
        ]);
    }
    public function getuser(Request $request)    {
        $user = JWTAuth::toUser($request->token);
        return response()->json([
            "user" =>  $user,
        ]);
    }
    public function logout(Request $request)    {
        try {
            JWTAuth::invalidate($request->token);
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function update(Request $request,User $user)    {
        $data = $request->all();
        $vadidator = Validator::make($data,[
            "name" =>  "required|max:255",   
            "address" => "required|max:255",
            "phone" => "required|max:11",
            "email" => "required|email|max:255",
            "password" => 'required|string|min:8|max:50',
        ]);
        if($vadidator->fails()) {
            return response()->json([
                'error' => $vadidator->errors()->toJson()
            ],200);
        }
            $user->update([
                'name' => $request->name,
                'address'=> $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
            $user->save($data);
        return response()->json([
            "mess" => "User update successfully",
            "user" => $user,
        ]);
    }
    public function delete(User $user,$id)  {
        if(!$user = $user->find($id))  {
            return response()->json([
                "user" =>"null",
            ]);
        }
        else {
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'user deleted successfully'
            ], Response::HTTP_OK);
        }
    }
    public function emailreset(Request $request,User $user)    {
        $data = $request->all();
        $vadidator = Validator::make($data,[
            "email" => "required|email|max:255",
        ]);
        if($vadidator->fails()) 
        {   
            return response()->json(['error' => $vadidator->errors()->toJson()],200 );
        }
        try {
            $user = User::where('email',$request->email)->first();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "error" => $e->getMessage(),
            ]);
        }
        if($user == null)   {
            return response()->json([
                "mess" => "User null",
            ]);
        }
        $order = Str::random(8);
        Mail::to($user->email)->send(new Testmail($order));return response()->json([
            "email" => $user->email,
            "mess" => "sending forgot password",
        ]);

    }
    public function resetpassword(User $user,Request $request) {
        $data = $request->only('password');
        $vadidator = Validator::make($data,[
            'password' => 'string|min:8|max:50'
        ]);
        if ($vadidator->fails()) {
            return response()->json(['error' => $vadidator->errors()->toJson()], 200);
        }
        $user->update([
            $user->password = bcrypt($request->password),
           ]);
        $user->save();
        return response()->json([
            "mess" => "forgot password sucessfully",
        ]);
    }
}
