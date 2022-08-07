<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const USER_WALLET = 1;
    const MERCHANT_WALLET = 2;

    /** 
     * Wallet Balance
     * 
     * @return HasOne 
     */
    public function walletBalance(): HasOne
    {
        return $this->hasOne(WalletBalance::class, 'walletId');
    }

    /** 
     * Users  Wallet Balance
     * 
     * @return HasOne 
     */
    public function usersWalletBalance()
    {
        return $this->hasOneThrough(
            WalletBalance::class, Wallet::class, 'userId', 'walletId'
        );
    }

    /** 
     * Merchant Wallet Balance 
     * 
     * @return HasOne 
     */
    public function merchantWalletBalance()
    {
        return $this->hasOne(WalletBalance::class, 'walletId');
    }

    /** 
     * Wallet transactions 
     * 
     * @return HasMany 
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'walletId');
    }

    /** 
     * Merchant Wallet Transactions 
     * 
     * @return HasMany
     */
    public function merchantWalletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'merchantId');
    }

    /** 
     * Latest Wallet Transaction 
     * 
     * @return HasOne 
     */
    public function latestWalletTransaction(): HasOne
    {
        return $this->hasOne(WalletTransaction::class, 'walletId')->latestOfMany();
    }

    /** 
     * Latest Wallet Transaction 
     * 
     * @return HasOne 
     */
    public function successLatestWalletTransaction(): HasOne
    {
        return 
            $this->hasOne(WalletTransaction::class, 'walletId')
            ->whereStatus('success')
            ->fetchFundings()
            ->latest('id');
            /* ->where(
                'transactionType', WalletTransaction::MESSAGE_FUND_WALLET_PAYSTACK
            )->orWhere(
                function ($query) {
                    $query
                        ->where(
                            'transactionType', 
                            WalletTransaction::MESSAGE_FUND_FROM_MERCHANT
                        );
                }
            ) */
            // ->latestOfMany();
            // ->fetchFundings();
    }

    /** 
     * Latest Merchant Wallet Transaction 
     * 
     * @return HasOne 
     */
    public function latestMerchantWalletTransaction(): HasOne
    {
        return $this->hasOne(WalletTransaction::class, 'merchantId')->latestOfMany();
    }

    /** 
     * User relationship 
     * 
     * @return BelongsTo 
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }

    /** 
     * Admin user relationship 
     * 
     * @return BelongsTo
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'merchantId');
    }

    /** 
     * Merchant Relationship 
     * 
     * @return BelongsTo
     */
    public function merchant()
    {
        // return $this->hasOne(Merchant::class, 'merchantId');
        return $this->belongsTo(Merchant::class, 'merchantId');
    }

    /** 
     * Get Created AT as a proper formated attribute
     * 
     * @return Carbon 
     */
    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d/m/Y  h:iA');
    }
}
