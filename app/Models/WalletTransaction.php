<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Merchant;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public $timestamps = false;

    const MESSAGE_ADD_BALANCE = "Added fund to beneficiary";
    const MESSAGE_REMOVE_BALANCE = "Remove fund from beneficiary";
    const MESSAGE_REMOVE_CREDIT = "Debit for credit";
    const MESSAGE_FUND_FROM_MERCHANT = "Fund account(Merchant)";
    const MESSAGE_FUEL_PURCHASE_MERCHANT = "Fuel Purchase";
    const MESSAGE_FUEL_PURCHASE_CREDIT_MERCHANT = "Fuel Purchase on Credit";
    const MESSAGE_FUND_WALLET_MERCHANT = "Wallet Funding";
    const MESSAGE_WALLET_DEBIT_MERCHANT = "Wallet Debit";
    const MESSAGE_WALLET_DEBIT_FROM_BENEFICIARY = "Beneficiary purcahse fuel";
    const MESSAGE_FUND_WALLET_PAYSTACK = "fund with card(Paystack)";

    protected $casts = [
        'transactionDate' => 'datetime:d/m/Y  h:iA',
    ];

    /** 
     * Add Transaction   
     * 
     * @param $user  
     * @param $request   
     * @param $merchant  
     * @param $attendant 
     * 
     * @return create
     */
    public static function addTransaction(
        $user, $request, $merchant = null, $attendant = null
    ) {
        $user->wallet->walletTransactions()->create(
            [
                'merchantId' => $merchant,
                'attendantId' => $attendant,
                'amount' => $request->amount,
                'transactionType' => $request->transactionType,
                'status' => $request->status,
                'ref' => $request->ref,
                'trans' => $request->trans,
                'transactionDate' => Carbon::now()->toDateTimeString()
            ]
        );
    }

    /** 
     * AddTransactionMerchant   
     * 
     * @param $attendant  
     * @param $amount   
     * @param $message  
     * @param $ref  
     * @param $trans  
     * 
     * @return create
     */
    public static function addTransactionMerchant(
        $attendant, $amount, $message, $ref, $trans
    ) {
        $attendant->merchantWallet->walletTransactions()->create(
            [
                'merchantId' => $attendant->merchantId,
                'attendantId' => $attendant->id,
                'amount' => $amount,
                'transactionType' => $message,
                'status' => 'success',
                'ref' => $ref,
                'trans' => $trans,
                'transactionDate' => Carbon::now()->toDateTimeString()
            ]
        );
    }

    /** 
     * TransRequest
     * 
     * @return random_int    
     */
    public static function transRequest()
    {
        return random_int(1000000000, 9999999999);
    }

    /** 
     * RefRequest
     * 
     * @return random_int    
     */
    public static function refRequest()
    {
        return random_int(1000000000000, 9999999999999);
    }

    /** 
     * PassParameterOfObject
     * 
     * @param $spendLimit  
     * @param $message  
     * 
     * @return $request   
     */
    public static function passParameterOfObject($spendLimit, $message = null)
    {
        $data = collect(
            [
                'ref' => static::refRequest(),
                'trans' => static::transRequest(),
                'amount' => $spendLimit,
                'transactionType' => $message??"Fund beneficiary account(Wallet)",
                'status' => "success"
            ]
        );

        $data->toJson();
        $request = json_decode($data);
        return $request;
    }

    /**
     * Latest Wallet Transaction 
     * 
     * @return latestOfMany
     */
    public function latestWalletTransaction() : HasOne
    {
        return $this->hasOne(WalletTransaction::class, 'walletId')->latestOfMany();
    }

    /**
     * Merchant 
     * 
     * @return belongsTo
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchantId');
    }

    /**
     * Attendant 
     * 
     * @return belongsTo
     */
    public function attendant()
    {
        return $this->belongsTo(AdminUser::class, 'attendantId');
    }

    /**
     * Wallet 
     * 
     * @return belongsTo
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'walletId');
    }

    /**
     * Get Wallet Transaction Date Attribute 
     * 
     * @return Carbon
     */
    public function getTransactionDateAttribute()
    {
        return Carbon::parse($this->attributes['transactionDate'])
        ->format('d/m/Y  h:iA');
    }

    /** 
     * Fetch only wallet transactions through funding
     * 
     * @param $query 
     * 
     * @return Builder 
     */
    public function scopeFetchFundings($query)
    {
        return $query->where(
            'transactionType', static::MESSAGE_FUND_WALLET_PAYSTACK
        )->orWhere(
            function ($query) {
                $query
                    ->where(
                        'transactionType', static::MESSAGE_FUND_FROM_MERCHANT
                    );
            }
        );
    }

    /** 
     * Weekly
     * 
     * @param $query 
     * 
     * @return Builder 
     */
    public function scopeWeekly($query)
    {
        return $query
            ->where('status', 'success')
            ->whereBetween(
                'transactionDate', [Carbon::now()->startOfWeek(), 
                Carbon::now()->endOfWeek()]
            );
    }

    /** 
     * Monhtly returns
     * 
     * @param $query 
     * 
     * @return Builder 
     */
    public function scopeMonthly($query)
    {
        return $query
            ->where('status', 'success')
            ->whereMonth('transactionDate', Carbon::now()->month)
            ->whereYear('transactionDate', Carbon::now()->year);
        /* ->whereMonth('transactionDate', date('m'))
        ->whereYear('transactionDate', date('Y')) */
    }

    /** 
     * Total Sales
     * 
     * @param $query 
     * 
     * @return Builder 
     */
    public function scopeTotalFunds($query)
    {
        return $query
            ->where('status', 'success');
    }

    /** 
     * Total Sales
     * 
     * @param $query 
     * 
     * @return Builder 
     */
    public function scopeTotalDailyFunds($query)
    {
        return $query
            ->where('status', 'success')
            ->whereDate('transactionDate', now()->format('Y-m-d'));
    }

    // fund wallet with e-Naira
    public function fundwallet(Request $request, User $user){

        $http = Http::post(
            'https://rgw.k8s.apis.ng/centric-platforms/uat/CreateInvoice', [
                {
                    "amount": 100,
                    "narration": "Testing Payment",
                    "reference": "NXG2638494749493",
                    "product_code": "001",
                    "channel_code": "APISNG"
                }
            ]
            );
            return response()->json(
                [
                    'message' => 'Your account has been successfully funded',
                    'data' => $user->wallet->latestWalletTransaction
                ]
            );
    }

}
