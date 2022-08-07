<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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


}
