<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Merchant;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\{Request, Response};
use Illuminate\Http\Resources\Json\JsonResource;

class MerchantController extends Controller
{
      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() 
    {
        abort_unless(
            auth()->user()->tokenCan('merchant.index'),
            Response::HTTP_FORBIDDEN
        );

        $merchants = Merchant::query()
            ->when(
                request('status'), 
                fn($builder) => $builder->where('status', request('status')),
                fn($builder) => $builder
            )
            ->with(
                [
                    'wallet:id,userId,merchantId', 
                    'wallet.walletBalance:id,walletId,balance'
                ]
            )
            ->withCount(['attendants'])
            ->latest('id')
            ->paginate(20);

        return MerchantResource::collection( 
            $merchants 
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request 
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) 
    {
        abort_unless(
            auth()->user()->tokenCan('merchant.create'),
            Response::HTTP_FORBIDDEN
        );

        $admin = auth()->user();

        $this->authorize('update', auth()->user());

        $validate = (new MerchantValidator())->validate(
            $merchant = new Merchant(),
            $request->all()
        );

        abort_unless(
            $admin->userRoleId !== UserRole::USER_ADMIN || 
            $admin->userRoleId !== UserRole::USER_SUPPORT ||
            $admin->userRoleId !== UserRole::USER_MERCHANT,
            401,
            "You are not authorized to add merchant"
        );

        /* abort_if(
            $admin->userRoleId !== UserRole::USER_ADMIN || 
            $admin->userRoleId !== UserRole::USER_SUPPORT ||
            $admin->userRoleId !== UserRole::USER_MERCHANT,
            401,
            "You are not authorized to add merchant"
        ); */

        // Naming image can come in this format
        /* $fileName = pathinfo(
            $request->file('logo')->hashName(), 
            PATHINFO_FILENAME
        )
            .'-'.now()->timestamp
            .'.'.$request->file('logo')->extension(); */

        if ($request->file('logo')) {
            $path = $request->file('logo')->store('/public/images', 's3');
            $validate['logo'] = $path;
        }
        
        $validate['addedBy'] = $admin->id;

        $merchant->fill($validate)->save();

        $wallet = $merchant->wallet()->create(
            [
                'walletNo' => 'FC'. random_int(10000000, 99999999),
                'walletType' => Wallet::MERCHANT_WALLET
            ]
        );

        $wallet->walletBalance()->create(
            [
                'balance' => 0.00
            ]
        );

        // return MerchantResource::make( $merchant );
        return response()->json(
            [
                'message' => "You have successfully created a merchant"
            ]
        );

    }

    /**
     * Display the specified resource.
     *
     * @param Merchant $merchant 
     * 
     * @return \Illuminate\Http\Response
     */
    public function show(Merchant $merchant) : JsonResource
    {
        abort_unless(
            auth()->user()->tokenCan('merchant.show'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('update', auth()->user());

        return MerchantResource::make($merchant);
    }

    /**
     * Display the specified resource.
     *
     * @param Merchant $merchant 
     * 
     * @return \Illuminate\Http\Response
     */
    public function displayMerchantDetails(Merchant $merchant) : JsonResource
    {
        abort_unless(
            auth()->user()->tokenCan('merchant.show'),
            Response::HTTP_FORBIDDEN
        );

        $user = auth()->user();
        abort_if(
            $user->merchantId !== $merchant->id,
            403,
            'You not authorized to access this route'
        );

        return MerchantResource::make($merchant);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request 
     * @param Merchant                 $merchant 
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Merchant $merchant)
    {
        abort_unless(
            auth()->user()->tokenCan('merchant.update'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('update', $merchant);

        $attributes = (new MerchantValidator())->validate(
            $merchant, $request->all()
        );
        if ($request->file('logo')) {
            Storage::disk('s3')->delete($merchant->logo);
            $path = $request->file('logo')->store('/public/images', 's3');
            $attributes['logo'] = $path;
        }

        $merchant->fill($attributes)->save();

        /* return MerchantResource::make( 
            $merchant 
        ); */
        return response()->json(
            [
                'message' => 'Merchant details update successfully'
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Merchant $merchant 
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy(Merchant $merchant)
    {
        abort_unless(
            auth()->user()->tokenCan('merchant.delete'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('delete', auth()->user());

        // $merchant->delete();
        $merchant->update(['status' => Merchant::ACCOUNT_INACTIVE]);

        return response()->json(
            [
                'status' => 'Account is deactivated'
            ]
        );
    }

    /**  
     * Update merchant details 
     * 
     * @param Request  $request 
     * @param Merchant $merchant 
     * 
     * @return response 
     */
    public function updateMerchantDetails(Request $request, Merchant $merchant)
    {
        abort_unless(
            auth()->user()->tokenCan('merchant-update.update'),
            Response::HTTP_FORBIDDEN
        );

        $attributes = (new MerchantValidator())->validate(
            $merchant, $request->all()
        );
        if ($request->file('logo')) {
            $request->file('logo')->storePublicly('/public/images');
        }

        $merchant->fill($attributes)->save();

        return response()->json(
            [
                'message' => 'Merchant details updated successfully'
            ]
        );
    }

    /**
     * Banks name 
     * 
     * @return Response
     */
    public function banks()
    {
        return BankInfo::fetchBanks();
    }

    public function merchantPaymentHistory() {
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

        $merchantPaymentDetails = DB::table('merchant_payments')
            ->join('merchants', 'merchant_payments.merchantId', '=', 'merchants.id')
            ->join('users', 'merchant_payments.userId', 'users.id')
            ->select('users.mobileNumber as customerId','users.firstName', 'users.lastName', 'merchants.merchantName as name', 'merchant_payments.amount as amount', 'merchant_payments.transref as transactionReference', 'merchant_payments.transDate as transactionDate', 'merchant_payments.status as status')->orderBy('merchant_payments.id', 'desc')
            ->paginate(20);
        
        return response()->json( 
            $merchantPaymentDetails
        );
        
    }
}
