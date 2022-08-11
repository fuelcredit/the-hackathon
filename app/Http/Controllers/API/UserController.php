<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\{Request, Response};
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\{Hash, Http};

class UserController extends Controller
{
    public function index()
    {
       
    }

    // create e-Naira Consumer
    public function createConsumer(Request $request) {
        $user_id = Auth::user()->id;
        $data = $request->validate([
            'account_no'=> 'required|max:191',
            'password'=> 'required',
            'bvn'=> 'required',
        ],
        [
            'account_no.required'=> 'First Name is required',
            'password.required' => 'Password is required',
            'bvn.required' => 'BVN is required'
        ]);

        $client = new Client();
        $params = [
                "channel_code" => "APISNG",
                "customer_tier" => "2",
                "reference" => $this->generateRandomString(19),
                "account_no" => $data['account_no'],
                "bvn" => $data['bvn'],
                "password" => $data['password'],
                // "nin" => ""
        ];

        $headers = [
            'ClientId' => env('ENAIRA_CLIENT_ID'),
            'Cache-Control' => 'no-cache'
        ];

        $url = env('ENAIRA_URL').'/CreateConsumer';

        $response = $client->request('POST', $url, [
            'json' => $params,
            'headers' => $headers,
            'verify'  => false,
        ]);

        //$responseBody = json_decode($response->getBody()->getContents());
        //dd($responseBody);
        if($responseBody->response_code == 00) {
            User::where(['id'=>$user_id])->update(['channel_code'=>$channel_code,'customer_tier'=>$customer_tier, 'reference'=>$data['reference'],'account_no'=>$data['account_no'],
                    'bvn'=>$data['bvn'], 'password'=>$data['password']]);
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
