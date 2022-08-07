<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelPurchase extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    public $timestamps = false;

    public const STATUS_PENDING = 0;
    public const STATUS_ACTIVE = 1;

    protected $casts = [
        'purchaseAmount' => 'decimal:2',
        'dateCreated' => 'datetime:d/m/Y  h:iA',
        'status' => 'integer'
    ];

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
     * Merchant
     * 
     * @return belongsTo 
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchantId');
    }

    /** 
     * User
     * 
     * @return belongsTo 
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    /**  
     * Select fields
     * 
     * @param $query 
     * 
     * @return Builder 
     */
    public function scopeSelect($query)
    {
        $query->select(
            [
                'id', 'userId', 'merchantId', 'purchaseAmount',
                'status', 'dateCreated', 'trans'
            ]
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
            ->where('status', static::STATUS_ACTIVE)
            ->whereBetween(
                'dateCreated', [Carbon::now()->startOfWeek(), 
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
            ->where('status', static::STATUS_ACTIVE)
            ->whereMonth('dateCreated', Carbon::now()->month)
            ->whereYear('dateCreated', Carbon::now()->year);
        /* ->whereMonth('dateCreated', date('m'))
        ->whereYear('dateCreated', date('Y')) */
    }

    /** 
     * Total Sales
     * 
     * @param $query 
     * 
     * @return Builder 
     */
    public function scopeTotalSales($query)
    {
        return $query
            ->where('status', static::STATUS_ACTIVE);
    }

    /** 
     * Total Sales
     * 
     * @param $query 
     * 
     * @return Builder 
     */
    public function scopeTotalDailySales($query)
    {
        return $query
            ->where('status', static::STATUS_ACTIVE)
            ->whereDate('dateCreated', now()->format('Y-m-d'));
    }

    /** 
     * Attendant
     * 
     * @param $query 
     * @param $user  
     * 
     * @return Builder 
     */
    public function scopeAttendant($query, $user)
    {
        $query
            ->where('attendantId', $user->id);
    }

    /** 
     * Merchant
     * 
     * @param $query 
     * @param $user  
     * 
     * @return Builder 
     */
    public function scopeMerchant($query, $user)
    {
        $query
            ->where('merchantId', $user->merchantId);
    }

    /** 
     * Betewen
     * 
     * @param $query 
     * @param $from 
     * @param $to 
     * 
     * @return Builder 
     */
    public function scopeActiveBetween($query, $from, $to)
    {
        $query->whereStatus(static::STATUS_ACTIVE)
            ->betweenDates($from, $to);
    }

    /** 
     * Betwwen Dates
     * 
     * @param $query 
     * @param $from 
     * @param $to   
     * 
     * @return Builder 
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        $query->where(
            function ($query) use ($from, $to) {
                $query->whereBetween('dateCreated', [$from, $to]);
            }
        );
    }

    /** 
     * Get Created At date in proper format
     * 
     * @return Carbon  
     */
    public function getDateCreatedAttribute()
    {
        return Carbon::parse($this->attributes['dateCreated'])
        ->format('d/m/Y  h:iA');
    }


}
