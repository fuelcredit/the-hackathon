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

    


}
