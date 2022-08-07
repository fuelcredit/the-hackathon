<?php

namespace App\Http\Controllers\API;

use App\Models\Merchant;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{User, FuelPurchase};
use Illuminate\Http\{Request, Response};

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

     /** 
     * Transaction By Merchant
     * 
     * @param Merchant $merchant instance 
     * 
     * @return json 
     */
    public function transactionByMerchant(Merchant $merchant)
    {
        abort_unless(
            auth()->user()->tokenCan('admin.merchant.index'),
            Response::HTTP_FORBIDDEN
        );

        $fuelPurchased = FuelPurchase::query()
            ->where('merchantId', $merchant->id)
            ->when(
                request('status') == 'active',
                fn($builder) =>
                    $builder->where('status', FuelPurchase::STATUS_ACTIVE),
                fn($builder) => $builder
            )
            ->when(
                request('status') == 'pending',
                fn($builder) =>
                    $builder->where('status', FuelPurchase::STATUS_PENDING),
                fn($builder) => $builder
            )
            ->paginate(20);

        return response()->json( 
            $fuelPurchased
        );
    }

    /** 
     * Get all Sales by merchant
     * 
     * @param Merchant $merchant instance 
     * 
     * @return merchant 
     */
    public function getAllSalesByMerchant(Merchant $merchant)
    {
        return $merchant->with('fuelSales')->get();
    }

    /** 
     * Transaction By Merchant
     * 
     * @param AdminUser $user instance 
     * 
     * @return json 
     */
    public function transactionByMerchantAttendant(AdminUser $user)
    {
        abort_unless(
            auth()->user()->tokenCan('admin.merchant-attendant.index'),
            Response::HTTP_FORBIDDEN
        );

        $fuelPurchased = FuelPurchase::query()
            ->select()
            ->where('attendantId', $user->id)
            ->where('merchantId', $user->merchantId)
            ->when(
                request('status') == 'active',
                fn($builder) =>
                    $builder->where('status', FuelPurchase::STATUS_ACTIVE),
                fn($builder) => $builder
            )
            ->when(
                request('status') == 'pending',
                fn($builder) =>
                    $builder->where('status', FuelPurchase::STATUS_PENDING),
                fn($builder) => $builder
            )
            ->paginate(20);

        return response()->json( 
            $fuelPurchased
        );
    }

    /** 
     * Admin Generate Sales Transaction
     * 
     * @param Merchant $merchant instance 
     * 
     * @return json 
     */
    public function adminGenerateSalesTransaction(Merchant $merchant)
    {
        abort_unless(
            auth()->user()->tokenCan('admin-daily-sales.store'),
            Response::HTTP_FORBIDDEN
        );

        // DB::enableQueryLog();

        $dailyTransaction = FuelPurchase::query()
            ->when(
                request('attendant'),
                fn($builder) => $builder
                    ->select(DB::raw('count(*) as total_purchase, sum(purchaseAmount) as total_daily_transaction'))
                    ->where('attendantId', request('attendant'))
                    ->where('merchantId', $merchant->id)
                    ->where('status', FuelPurchase::STATUS_ACTIVE)
                    ->whereDate('dateCreated', now()->format('Y-m-d'))
            )
            ->when(
                request('start-date') && request('end-date'),
                fn($builder) => $builder
                    ->select(DB::raw('count(*) as total_purchase, sum(purchaseAmount) as total_daily_transaction'))
                    ->where('merchantId', $merchant->id)
                    ->activeBetween(request('start-date'), request('end-date')),
                fn($builder) => $builder
                    ->select(DB::raw('count(*) as total_purchase, sum(purchaseAmount) as total_daily_transaction'))
                    ->where('merchantId', $merchant->id)
                    ->where('status', FuelPurchase::STATUS_ACTIVE)
                    ->whereDate('dateCreated', now()->format('Y-m-d'))
            )
            ->get();

            // dd(DB::getQueryLog());

        return response()->json($dailyTransaction);
    }

    public function fuelPurchaseHistory(Request $request) {
        abort_unless(
            auth()->user()->tokenCan('display-transaction-to-admin'),
            Response::HTTP_FORBIDDEN
        );

        $user = auth()->user();

        abort_unless(
            $user->userRole !== UserRole::USER_ADMIN 
            || $user->userRole !== UserRole::USER_SUPPORT,
            403,
            'You dont have access to authorized this'
        );

        $fuelPurchaseDetails = DB::table('fuel_purchases')
            ->join('users', 'fuel_purchases.userId', '=', 'users.id')
            ->join('merchants', 'fuel_purchases.merchantId', '=', 'merchants.id')
            ->select('users.firstName', 'users.lastName', 'fuel_purchases.purchaseAmount as amount', 'fuel_purchases.trans as transactionReference', 'fuel_purchases.dateCreated as transactionDate', DB::raw('if(fuel_purchases.status=1, "Success", "Failed" ) as status'))
            ->selectRaw("round((fuel_purchases.purchaseAmount / 165), 2) as numberOfLiters")
            ->where('fuel_purchases.merchantId', $request->merchantId)
            ->paginate(20);

        
        $todaySales = FuelPurchase::query()->totalDailySales()
            ->where('merchantId', $request->merchantId)
            ->sum('purchaseAmount');
        $weeklySales = FuelPurchase::query()->weekly()
            ->where('merchantId', $request->merchantId)
            ->sum('purchaseAmount');
        $monthlySales = FuelPurchase::query()->monthly()
            ->where('merchantId', $request->merchantId)
            ->sum('purchaseAmount');
        $totalSales = FuelPurchase::query()->totalSales()
            ->where('merchantId', $request->merchantId)
            ->sum('purchaseAmount');
        
        return response()->json([ 
            $fuelPurchaseDetails,
            "today" => $todaySales,
            "this_week" => $weeklySales,
            "this_month" => $monthlySales,
            "total" => $totalSales
        ]);
    }


}
