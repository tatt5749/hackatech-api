<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FixedController;
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

Route::prefix('auth')->group(function () {
    Route::post('/login/email', [AuthController::class, 'loginEmail']);
    Route::post('/login/phone', [AuthController::class, 'loginPhone']);
});
    

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('fixed')->group(function () {
        
        Route::get('/overview', [FixedController::class, 'overview']);
        Route::get('/overview/stake', [FixedController::class, 'stakeOverview']);
        Route::get('/overview/personal', [FixedController::class, 'personalOverview']);
        Route::get('/stakes', [FixedController::class, 'getStakes']);
        Route::get('/transaction/status', [FixedController::class, 'getTransactionStatus']);
        Route::get('/approveStake', [FixedController::class, 'approveStakeStatus']);
        Route::get('/approveClaim', [FixedController::class, 'approveClaimStatus']);
        
        Route::put('/approveStake', [FixedController::class, 'approveStake']);
        Route::put('/approveClaim', [FixedController::class, 'approveClaim']);
        Route::put('/claim', [FixedController::class, 'claim']);
        Route::post('/stake', [FixedController::class, 'stake']);
        Route::post('/unstake', [FixedController::class, 'unstake']);
        
        Route::put('/setupWallet', [FixedController::class, 'setupWallet']);
        Route::post('/send', [FixedController::class, 'send']);
    });
});



