<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'mobileNumber',
        'bvn',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'bvn' => 'encrypted',
    ];

     /** 
     * User Wallet
     * 
     * @return hasOne 
     */
    public function wallet() : HasOne 
    {
        return $this->hasOne(Wallet::class, 'userId');
    }

    /** 
     * User Wallet
     * 
     * @return hasOne 
     */
    public function totalWalletBalance()  
    {
        return $this->hasManyThrough(
            WalletBalance::class, Wallet::class, 'userId', 'walletId'
        );
    }

     /** 
     * User fuel purchases
     * 
     * @return hasMany 
     */
    public function fuelPurchases() : HasMany 
    {
        return $this->hasMany(FuelPurchase::class, 'userId');
    }

    /** 
     * User total fuel purchase
     * 
     * @return hasMany 
     */
    public function totalFuelPurchase() 
    {
        return $this->hasMany(
            FuelPurchase::class, 'userId', 'id'
        );
        /* return $this->hasManyThrough(
            FuelPurchase::class, Wallet::class, 
            'userId', 'userId'
        ); */
    }

    /** 
     * User total fuel purchase
     * 
     * @return hasMany 
     */
    public function totalFuelPurchases() 
    {
        return $this->hasMany(FuelPurchase::class, 'userId')
            ->where('status', FuelPurchase::STATUS_ACTIVE)->sum('purchaseAmount');
    }

    /** 
     * Count User Fuel Purchases
     * 
     * @return hasMany 
     */
    public function countFuelPurchases() 
    {
        return $this->hasMany(FuelPurchase::class, 'userId')
            ->where('status', FuelPurchase::STATUS_ACTIVE)->count('purchaseAmount');
    }

    /** 
     * Count Filling Station Where User Purchases Fuel
     * 
     * @return hasMany 
     */
    public function countFillingStationFuelPurchases() 
    {
        return $this->hasMany(FuelPurchase::class, 'userId')->distinct('merchantId')
            ->where('status', FuelPurchase::STATUS_ACTIVE)->count('purchaseAmount');
    }

    /** 
     * User Latest Fuel Purchase
     * 
     * @return hasOne 
     */
    public function latestFuelPurchases() : HasOne
    {
        return $this->hasOne(FuelPurchase::class, 'userId')
            ->where('status', FuelPurchase::STATUS_ACTIVE)
            ->latest('dateCreated');
    }

/** 
     * Checking User Wallet balance
     * 
     * @return string 
     */
    public function userBalance()
    {
        if (!$this->wallet->walletBalance()->exists()) {
            $balance = $this->wallet->walletBalance()->create(
                [
                    'balance' => 0.00,
                    'lastUpdated' => Carbon::now()->toDateTimeString()
                ]
            );
        } else {
            $balance = $this->wallet->walletBalance;
        }

        return $balance->balance;
    }

    /** 
     * Update User Wallet
     * 
     * @param $balance 
     * @param $amount 
     * 
     * @return update 
     */
    public function updateUserWallet($balance, $amount)
    {
        $this->wallet->walletBalance->update(
            [
                'balance' => $balance + $amount,
                'lastUpdated' => Carbon::now()->toDateTimeString()
            ]
        );
    }

     /** 
     * Create User Wallet
     * 
     * @return create 
     */
    public function userWallet()
    {
        $wallet = $this->wallet()->create(
            [
                'walletNo' => 'FC'. random_int(10000000, 99999999),
                'walletType' => Wallet::USER_WALLET
            ]
        );

        $wallet->walletBalance()->create(
            [
                'balance' => 0.00
            ]
        );
    }

     /** 
     * Total number of users
     * 
     * @param $query 
     * 
     * @return hasMany 
     */
    public function scopeTotalUsers($query) 
    {
        return $query->where(FuelPurchase::class, 'userId');
    }

}
