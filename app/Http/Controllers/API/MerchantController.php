<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Merchant;
use DB;
use Illuminate\Http\{Request, Response};
use GuzzleHttp\Client;


class MerchantController extends Controller
{
   public function registerMerchant(Request $request) {
    $data = $request->validate([
        'merchantName'=> 'required|max:191',
        'director_bvn'=> 'required|max:11',
        'email'=> 'required|email|max:191|unique:merchants',
        'password'=> 'required|confirmed',
        'password_confirmation' => 'required',
        'phoneNumber'=> 'required',
        'city'=> 'required',
        'state' => 'required',
        'address' => 'required'
    ],
    [
        'merchantName.required'=> 'Name is required',
        'director_bvn.required'=> 'Directors BVN is required',
        'email.required'=> 'Email is required',
        'email.unique' => 'Email has already been taken',
        'password.required' => 'Password is required',
        'password.confirmed' => 'Passwords do not match',
        'phoneNumber.required' => 'Phone Number is required',
        'city.required' => 'City is required',
        'state.required' => 'State is required',
        'address.required' => 'Address is required'
    ]);

     $merchant =Merchant::create([
        'merchantName'=> $data['merchantName'],
        'director_bvn'=> $data['director_bvn'],
        'phoneNumber'=> $data['phoneNumber'],
        'email'=> $data['email'],
        'password'=> Hash::make($data['password']),
        'city' => $data['city'],
        'state' => $data['state'],
        'address' => $data['address']
     ]);
     $token =$merchant->createToken('enairaToken')->plainTextToken;

     $response = [
        'merchant' => $merchant,
        'token' => $token

     ];
     return response($response, 201);

   }

   public function loginMerchant() {

   }

   public function logoutMerchant() {

   }

   public function createMerchant() {

   }
}
