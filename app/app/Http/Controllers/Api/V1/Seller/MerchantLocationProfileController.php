<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Seller\UpdateMerchantLocationProfileRequest;
use App\Http\Resources\MerchantLocationResource;
use App\Models\MerchantLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantLocationProfileController extends Controller
{
    /**
     * Show the full store profile for this merchant location.
     */
    public function show(Request $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $merchantLocation->load([
            'merchant',
            'merchantCategory',
            'state',
            'city',
            'tags',
        ]);

        $merchantLocation->loadCount(['qrCodes', 'transactions', 'coupons']);

        // Get the owner user from the pivot
        $owner = $merchantLocation->users()
            ->wherePivot('role', 'owner')
            ->first();

        // If no owner, fallback to requesting user
        $ownerUser = $owner ?? $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'location' => new MerchantLocationResource($merchantLocation),
                'owner' => [
                    'name' => $ownerUser?->name,
                    'email' => $ownerUser?->email,
                    'phone' => $ownerUser?->mobile,
                ],
            ],
        ]);
    }

    /**
     * Update the store profile for this merchant location.
     */
    public function update(UpdateMerchantLocationProfileRequest $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $merchantLocation->update($request->only([
            'branch_name',
            'address',
            'gst_number',
            'pan_number',
            'latitude',
            'longitude',
            'state_id',
            'city_id',
        ]));

        // Handle media uploads
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $merchantLocation->addMedia($file)->toMediaCollection('media');
            }
        }

        $merchantLocation->load(['merchant', 'merchantCategory', 'state', 'city', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Store profile updated successfully.',
            'data' => new MerchantLocationResource($merchantLocation),
        ]);
    }
}
