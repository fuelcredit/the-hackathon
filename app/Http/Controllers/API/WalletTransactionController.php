<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletTransactionController extends Controller
{
    public function fundWallet(Request $request, User $user){
        $user_id = Auth::user()->id;

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
}
