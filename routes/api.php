<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('auth', [AuthController::class, 'login'])->withoutMiddleware(['jwt.auth']);

Route::group(['middleware' => ['api']], function () {
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout');
    Route::get('/payment/success', [CheckoutController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/cancel', [CheckoutController::class, 'paymentCancel'])->name('payment.cancel');
});
