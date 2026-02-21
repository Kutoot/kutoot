<?php

use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\MerchantLocationController;
use App\Http\Controllers\Api\StampController;
use App\Http\Controllers\RazorpayWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Razorpay Webhooks (no auth, CSRF exempted)
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])
    ->name('webhooks.razorpay');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/merchant-locations', [MerchantLocationController::class, 'index']);

    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::get('/campaigns/{campaign}/bounty', [CampaignController::class, 'bounty']);

    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons/{coupon}/redeem', [CouponController::class, 'redeem']);

    Route::patch('/stamps/{stamp}/code', [StampController::class, 'updateCode'])
        ->name('api.stamps.update-code');
});
