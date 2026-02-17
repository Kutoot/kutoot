<?php

namespace App\Http\Controllers;

use App\Models\QrCode;

class QrScanController extends Controller
{
    public function scan(string $token)
    {
        $qrCode = QrCode::where('token', $token)
            ->where('status', 'linked')
            ->with('merchantLocation')
            ->firstOrFail();

        // Redirect to the merchant location's coupon page.
        // Assuming there's a route for merchants/locations/{id} or we just want coupons.index with a filter.
        // For now, let's redirect to coupons.index with the location selected if possible, or a specific merchant page.

        return redirect()->route('coupons.index', ['location' => $qrCode->merchant_location_id])
            ->with('message', 'Welcome to '.$qrCode->merchantLocation->branch_name);
    }
}
