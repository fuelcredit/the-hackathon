<?php

namespace App\Http\Controllers\API;

use App\Models\Merchant;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{User, FuelPurchase};
use Illuminate\Http\{Request, Response};

class FuelPurchaseController extends Controller
{
    public function buyFuel(Request $request) {
        
        $merchant_id = Auth::merchant()->id;

        $data = $request->validate([
            'purchaseAmount' => 'required|integer|min:50',
            'email' => 'required',
        ],
        [
            'purchaseAmount.required'=> 'Fuel Amount is required',
            'email.required' => 'Email is required'
        ]);

        $user = User::where('email', $data['email'])->first();
        $user_id = $user->id;

        $fuel = FuelPurchase::create(
            [
                'purchaseAmount' => $data['purchaseAmount'],
                'userId' => $user_id,
                'status' => "success",
                'merchantId' => auth()->merchant()->merchantId,
                'trans_ref' => $this->generateRandomString(19),
            ]
        );

        return response()->json(
            [
                'message' => 'Fuel purchase successfull',
            ]
        );
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
