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



}
