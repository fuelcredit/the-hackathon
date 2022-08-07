<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        $data = $request->validate([
            'firstName'=> 'required|max:191',
            'lastName'=> 'required|max:191',
            'email'=> 'required|email|max:191|unique:users',
            'password'=> 'required|confirmed|min:6',
            'mobileNumber'=> 'required',
            'bvn'=> 'required',
        ],
        [
            'firstName.required'=> 'First Name is required',
            'lastName.required'=> 'Last Name is required',
            'email.required'=> 'Email is required',
            'email.unique' => 'Email has already been taken',
            'password.required' => 'Password is required',
            'password.confirmed' => 'Passwords do not match',
            'mobileNumber.required' => 'Phone Number is required',
            'bvn.required' => 'BVN is required'
        ]);

         $user =User::create([
            'firstName'=> $data['firstName'],
            'lastName'=> $data['lastName'],
            'mobileNumber'=> $data['mobileNumber'],
            'email'=> $data['email'],
            'password'=> Hash::make($data['password']),
            'bvn' => $data['bvn']
         ]);
         $token =$user->createToken('enairaToken')->plainTextToken;

         $response = [
            'user' => $user,
            'token' => $token

         ];
         return response($response, 201);
    }

    public function login(Request $request){
        $data = $request->validate([
            'email'=> 'required|email|max:191',
            'password'=> 'required|string',

        ]);
        $user = User::where('email', $data['email'])->first();

            if(!$user || !Hash::check($data['password'], $user->password)){
                return response(['message'=>'InvalidCredential'], 401);
            }
            else{
                $token =$user->createToken('enairaTokenLogin')->plainTextToken;
                $response=[
                    'user' => $user,
                    'token'=> $token
                ];
                return response($response, 200);
            }
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return response (['message'=>"logged out successfully"]);
    }
}
