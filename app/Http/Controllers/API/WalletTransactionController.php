<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Auth;

class WalletTransactionController extends Controller
{
    public function fundWallet(Request $request) {
        $user_id = Auth::user()->id;
        $user = Auth::user();

        $data = $request->validate([
            'amount' => 'required',
            'narration' => 'required',
        ],
        [
            'amount.required' => 'Amount is required',
            'narration.required' => 'Narration is required'
        ]);

        $client = new Client();
        $params = [
                "amount" => $data['amount'],
                "narration" => $data['narration'],
                "reference" => $this->generateRandomString(19),
                "channel_code" => "APISNG",
                "product_code" => "001",
        ];

        $headers = [
            'ClientId' => env('ENAIRA_CLIENT_ID'),
            'Cache-Control' => 'no-cache'
        ];

        $url = env('ENAIRA_URL').'/CreateInvoice';

        $response = $client->request('POST', $url, [
            'json' => $params,
            'headers' => $headers,
            'verify'  => false,
        ]);

        $initial_balance = $user->wallet()->balance;
        

        $responseBody = json_decode($response->getBody()->getContents());

        if($responseBody->response_code == 00) {
            $total_amount = $data['amount'] + $initial_balance;
            Wallet::where(['userId'=>$user_id])->update(['userId'=> $user_id, 'wallet_balance'=>$total_amount]);
        }

        return response($response->getBody()->getContents());

    }

    public function payMerchant(){
        $merchant_id = Auth::merchant()->id;

        $data = $request->validate([
            'amount'=> 'required',
            'narration'=> 'required',
            'reference'=> 'required',
            'product_code'=> 'required',
            'channel_code'=> 'required',
        ],
        [
            'amount.required'=> 'Amount is required',
        ]);
        $client = new Client();
        $params = [
                "amount" => $data['amount'],
                "narration" => $data['narration'],
                "reference" => $data['reference'],
                "channel_code" => $data['channel_code'],
                "product_code" => $data['product_code'],
        ];

        $headers = [
            'ClientId' => env('ENAIRA_CLIENT_ID'),
            'Cache-Control' => 'no-cache'
        ];

        $url = env('ENAIRA_URL').'/CreateInvoice';

        $response = $client->request('POST', $url, [
            'json' => $params,
            'headers' => $headers,
            'verify'  => false,
        ]);

        
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

