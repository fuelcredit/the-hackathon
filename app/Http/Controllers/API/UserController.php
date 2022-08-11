<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\FuelPurchase;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\{Hash, Http};
use Illuminate\Http\{Request, Response};
use GuzzleHttp\Client;

class UserController extends Controller
{
    public function index()
    {
       
    }

    // create e-Naira Consumer
    public function createConsumer(Request $request) {
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
                "reference" => $this->generateRandomString(8),
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

        $responseBody = json_decode($response->getBody());
        //dd($responseBody);
        if($responseBody->response_code == 99) {
            $message = $responseBody->response_data;
            return response($message, 403);
        }else{
            
            return response($responseBody, 200);
        }
        
        //return response($responseBody);
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
