<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Seller\UpdateMerchantNotificationRequest;
use App\Models\MerchantLocation;
use App\Models\MerchantNotificationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantLocationNotificationController extends Controller
{
    /**
     * Get notification settings for this merchant location.
     */
    public function show(Request $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $setting = $merchantLocation->notificationSetting;

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $setting?->enabled ?? true,
                'channels' => $setting?->channels ?? MerchantNotificationSetting::defaultChannels(),
            ],
        ]);
    }

    /**
     * Update notification settings for this merchant location.
     */
    public function update(UpdateMerchantNotificationRequest $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $setting = $merchantLocation->notificationSetting()->updateOrCreate(
            ['merchant_location_id' => $merchantLocation->id],
            [
                'enabled' => $request->boolean('enabled'),
                'channels' => $request->input('channels'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated.',
            'data' => [
                'enabled' => $setting->enabled,
                'channels' => $setting->channels,
            ],
        ]);
    }
}
