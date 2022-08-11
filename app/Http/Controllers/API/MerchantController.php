<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Merchant;
use DB;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\{Hash, Http};
use GuzzleHttp\Client;
use Auth;


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

     $merchant = Merchant::create([
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

   public function loginMerchant(Request $request) {
    $data = $request->validate([
        'email'=> 'required|email|max:191',
        'password'=> 'required|string',

    ]);
    $merchant = Merchant::where('email', $data['email'])->first();

        if(!$merchant || !Hash::check($data['password'], $merchant->password)){
            return response(['message'=>'InvalidCredential'], 401);
        }
        else{
            $token =$merchant->createToken('enairaTokenLogin')->plainTextToken;
            $response=[
                'merchant' => $merchant,
                'token'=> $token
            ];
            return response($response, 200);
        }

   }

   public function logoutMerchant(Request $request) {
        Auth::merchant()->tokens()->delete();
        return response (['message'=>"logged out successfully"]);

   }

   public function createMerchant(Request $request) {
    
    $merchant_id = Auth::merchatnt()->id;
    $data = $request->validate([
        'account_no'=> 'required|max:191',
        'password'=> 'required',
        'director_bvn'=> 'required',
        'nin' => 'required',
        'user_name' => 'required',
        'city' => 'required',
        'state' => 'required',
        'wallet_category' => 'required'
    ],
    [
        'account_no.required'=> 'First Name is required',
        'password.required' => 'Password is required',
        'director_bvn.required' => 'BVN is required',
        'nin.required' => 'NIN is required',
        'user_name.required' => 'Username is required',
        'city.required' => 'City is required',
        'state.required' => 'State is required',
        'wallet_category.required' => 'Wallet Category is required'
    ]);

    $client = new Client();
    $params = [
        "channel_code" => "APISNG",
        "customer_tier" => "2",
        "reference" => $this->generateRandomString(19),
        "account_no" => $data['account_no'],
        "director_bvn" => $data['director_bvn'],
        "password" => $data['password'],
        "nin" => $data['nin'],
        "user_name" => $data['user_name'],
        "city" => $data['city'],
        "state" => $data['state'],
        "wallet_category" => "parent",

    ];

    $headers = [
        'ClientId' => env('ENAIRA_CLIENT_ID'),
        'Cache-Control' => 'no-cache'
    ];

    $url = env('ENAIRA_URL').'/CreateMerchant';

    $response = $client->request('POST', $url, [
        'json' => $params,
        'headers' => $headers,
        'verify'  => false,
    ]);

    //$responseBody = json_decode($response->getBody()->getContents());
    //dd($responseBody);
    if($responseBody->response_code == 00) {
        Merchant::where(['id'=>$merchant_id])->update(['feature_item'=>$feature_item,'status'=>$status, 'category_id'=>$data['category_id'],'product_name'=>$data['product_name'],
				'product_condition'=>$data['product_condition'], 'description'=>$data['description'],'state'=>$data['state'],'price'=>$data['price'],'lga'=>$data['lga'], 
                'phone'=>$data['phone']]);
    }
    
    return response($response->getBody()->getContents());

   }

   public  function generateRandomString($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
}
