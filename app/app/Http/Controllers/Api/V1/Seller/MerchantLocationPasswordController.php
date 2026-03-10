<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Seller\ChangeMerchantPasswordRequest;
use App\Models\MerchantLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class MerchantLocationPasswordController extends Controller
{
    /**
     * Change the authenticated user's password.
     */
    public function update(ChangeMerchantPasswordRequest $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->input('oldPassword'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->input('newPassword')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }
}
