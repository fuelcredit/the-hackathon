<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{AuthController, UserController, MerchantController, WalletTransactionController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
//Route::post('/logout', [AuthController::class, 'logout']);

Route::group(['middleware' => ['auth:sanctum']], function() {    
    Route::post('/logout',   [AuthController::class, 'logout']);

    Route::post('/merchant-logout',   [MerchantController::class, 'logoutMerchant']);

    Route::post('/create-merchant', [MerchantController::class, 'createMerchant']);
    Route::post('/create-consumer-wallet', [UserController::class, 'createConsumer']);

    Route::post('/fund-wallet', [WalletTransactionController::class, 'fundWallet']);
    Routee::post('/buy-fuel', [FuelPurchaseController::class, 'buyFuel']);
    Route::post('/pay-merchant', [WalletTransactionController::class, 'payMerchant']);
  });

Route::post('/register-merchant', [MerchantController::class, 'registerMerchant']);
Route::post('/login-merchant', [MerchantController::class, 'loginMerchant']);


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });