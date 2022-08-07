<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\{Request, Response};
use App\Http\Controllers\Controller;
use App\Models\{User, FuelPurchase};

class FuelPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $user 
     * 
     * @return \Illuminate\Http\Response 
     */

    public function index(User $user)
    {
        abort_unless(
            auth()->user()->tokenCan('user.index'),
            Response::HTTP_FORBIDDEN
        );

        $fuelPurchased = FuelPurchase::query()
            // ->select()
            ->where('userId', $user->id)
            ->where('status', FuelPurchase::STATUS_ACTIVE)
            ->latest('id')
            ->paginate(20);

        return response()->json( 
            $fuelPurchased
        );
    }

    

}
