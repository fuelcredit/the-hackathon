<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\{Response, Request};
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Merchant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public const ACCOUNT_ACTIVE = 1;
    public const ACCOUNT_INACTIVE = 0;

    protected $touches = ['attendants', 'wallet'];

    protected $casts = [
        'bankName' => 'encrypted',
        'tin' => 'encrypted',
        'accountName' => 'encrypted',
        'accountNumber' => 'encrypted'
    ];

    /** 
     * Wallet relationship
     * 
     * @return hasOne 
     */
    public function wallet() : HasOne 
    {
        return $this->hasOne(Wallet::class, 'merchantId');
    }

    /** 
     * Attendant relationship 
     * 
     * @return hasMany  
     */
    public function attendants(): HasMany
    {
        return $this->hasMany(AdminUser::class, 'merchantId');
    }

    /** 
     * Fuel Sales relationship
     * 
     * @return hasMany  
     */
    public function fuelSales(): HasMany
    {
        return $this->hasMany(FuelPurchase::class, 'merchantId');
    }

    /** 
     * Fuel Sales relationship
     * 
     * @return hasMany  
     */
    public function activeFuelPurchases() : HasMany
    {
        return $this->hasMany(FuelPurchase::class, 'merchantId')
            ->where('fuel_purchases.status', FuelPurchase::STATUS_ACTIVE);
    }

    /** 
     * Fuel Sales relationship total sum
     * 
     * @return hasMany  
     */
    public function scopeSumFuelSales()
    {
        return $this->fuelSales()->sum('purchaseAmount');
    }
    

    /** 
     * User Wallet
     * 
     * @return hasOne 
     */
    public function totalWalletBalance()  
    {
        return $this->hasManyThrough(
            WalletBalance::class, Wallet::class, 'merchantId', 'walletId'
        );
    }

    /**
     * Wallet relationship
     *
     * @return relatinship Hasone
     */
    public function walletTransactions() : HasManyThrough
    {
        return $this->hasManyThrough(
            WalletTransaction::class, Wallet::class, 'merchantId', 'walletId'
        );
    }


}
