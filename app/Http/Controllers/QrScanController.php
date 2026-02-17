<?php

namespace App\Http\Controllers;

use App\Enums\QrCodeStatus;
use App\Models\QrCode;

class QrScanController extends Controller
{
    public function scan(string $token)
    {
        $qrCode = QrCode::where('token', $token)
            ->where('status', QrCodeStatus::Linked)
            ->with('merchantLocation')
            ->firstOrFail();

        return redirect()->route('coupons.index', ['location' => $qrCode->merchant_location_id])
            ->with('message', 'Welcome to '.$qrCode->merchantLocation->branch_name);
    }
}
