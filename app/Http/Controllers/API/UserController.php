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
            "channelCode" => "APISNG",
            "uid" => "22142360969",
            "uidType" => "BVN",
            "reference" => "NXG3547585HGTKJHGO",
            "title" => "Mr",
            "firstName" => "Ifeanyichukwu",
            "middleName" => "Gerald",
            "lastName" => "Mbah",
            "userName" => "icmbah@cbn.gov.ng",
            "phone" => "08036349590",
            "emailId" => "icmbah@cbn.gov.ng",
            "postalCode" => "900110",
            "city" => "gwarinpa",
            "address" => "Lagos Estate, Abuja",
            "countryOfResidence" => "NG",
            "tier" => "2",
            "accountNumber" => "0025592222",
            "dateOfBirth" => "31/12/1987",
            "countryOfBirth" => "NG",
            "password" => "1234567890",
            "remarks" => "Passed",
            "referralCode" => "@imbah.01"
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
