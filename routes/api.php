<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
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
    

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


