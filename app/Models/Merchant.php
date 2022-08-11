<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\{Response, Request};
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Merchant extends Model
{
    use HasFactory, HasApiTokens;

    
    protected $casts = [
        'bankName' => 'encrypted',
        'tin' => 'encrypted',
        'accountName' => 'encrypted',
        'accountNumber' => 'encrypted'
    ];

    


}
