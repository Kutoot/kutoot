<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\QrCodeStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\MerchantLocationResource;
use App\Models\QrCode;
use Illuminate\Http\JsonResponse;

/**
 * @tags QR
 */
class QrScanController extends Controller
{
    /**
     * Scan QR code
     *
     * Resolves a QR code token and returns the linked merchant location
     * with available coupons info. Only linked/active QR codes are valid.
     *
     * @response 200 { "data": { "merchant_location": {}, "message": "Welcome to Store Name" } }
     * @response 404 { "message": "QR code not found or not linked." }
     */
    public function scan(string $token): JsonResponse
    {
        $qrCode = QrCode::where('token', $token)
            ->where('status', QrCodeStatus::Linked)
            ->with('merchantLocation.merchant')
            ->first();

        if (! $qrCode) {
            return response()->json([
                'error' => 'QR code not found or not linked to any location.',
            ], 404);
        }

        // Log the scan for analytics
        activity()
            ->performedOn($qrCode)
            ->event('scanned')
            ->withProperties([
                'merchant_location_id' => $qrCode->merchant_location_id,
                'branch_name' => $qrCode->merchantLocation->branch_name,
            ])
            ->log("QR code {$qrCode->unique_code} was scanned");

        return response()->json([
            'data' => [
                'merchant_location' => new MerchantLocationResource($qrCode->merchantLocation),
                'message' => 'Welcome to '.$qrCode->merchantLocation->branch_name,
            ],
        ]);
    }
}
