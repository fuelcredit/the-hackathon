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
        $client = new Client();
        $params = [
                "channel_code" => "APISNG",
                "customer_tier" => "2",
                "reference" => "NXA34567898FGHJJB1",
                "account_no" => "0689658501",
                "bvn" => "22152793496",
                "password" => "Password10$$",
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
        dd($responseBody);
        
        return response($responseBody);
    }


    }
