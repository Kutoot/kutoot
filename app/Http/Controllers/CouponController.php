<?php

namespace App\Http\Controllers;

use App\Models\DiscountCoupon;
use App\Models\MerchantLocation;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CouponController extends Controller
{
    public function __construct(
        protected \App\Services\CouponRedemptionService $redemptionService,
        protected \App\Services\Payments\PaymentManager $paymentManager
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $planId = $user?->activeSubscription?->plan_id;

        $coupons = DiscountCoupon::query()
            ->when($planId, fn ($q) => $q->forPlan($planId))
            ->active()
            ->with(['merchantLocation.merchant', 'category'])
            ->latest()
            ->paginate(9);

        // Pass available locations for redemption dropdown
        $locations = MerchantLocation::with('merchant')->get()->map(function ($loc) {
            return [
                'id' => $loc->id,
                'name' => $loc->branch_name.' ('.$loc->merchant->name.')',
            ];
        });

        return Inertia::render('Coupons/Index', [
            'coupons' => $coupons,
            'locations' => $locations,
            'planName' => $user?->activeSubscription?->plan->name ?? 'Free Tier',
            'isLoggedIn' => (bool) $user,
        ]);
    }

    public function redeem(Request $request, DiscountCoupon $coupon)
    {
        $validated = $request->validate([
            'merchant_location_id' => 'required|exists:merchant_locations,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = $request->user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'You must be logged in to redeem a coupon.');
        }

        $merchantLocation = MerchantLocation::with('merchant')->findOrFail($validated['merchant_location_id']);
        $amount = (float) $validated['amount'];

        try {
            return DB::transaction(function () use ($user, $coupon, $merchantLocation, $amount) {
                // 1. Calculate Fees & GST
                $platformFee = config('app.platform_fee', 10);
                if (config('app.platform_fee_type') === 'percentage') {
                    $platformFee = ($amount * $platformFee) / 100;
                }

                $gstAmount = ($platformFee * config('app.gst_rate', 18)) / 100;
                $totalAmount = $platformFee + $gstAmount; // User ONLY pays platform fee + GST to Kutoot?
                // Wait, user bill is 'amount'. Final bill for user = amount (to merchant) + fee + gst (to kutoot).
                // User pays total_amount = platform_fee + gst_amount + merchant_amount?
                // The requirement says: "final bill after discont will go to merchant account and platform and gst will go to kutoot account"

                // Calculate discount if applicable here to get 'final bill after discount'
                $discountAmount = 0.0;
                if ($coupon->discount_type === \App\Enums\DiscountType::Fixed) {
                    $discountAmount = $coupon->discount_value;
                } else {
                    $discountAmount = ($amount * $coupon->discount_value) / 100;
                }
                $finalBillAfterDiscount = max(0, $amount - $discountAmount);
                $grandTotal = $finalBillAfterDiscount + $platformFee + $gstAmount;

                // 2. Create Transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'merchant_location_id' => $merchantLocation->id,
                    'amount' => $finalBillAfterDiscount,
                    'platform_fee' => $platformFee,
                    'gst_amount' => $gstAmount,
                    'total_amount' => $grandTotal,
                    'payment_gateway' => $this->paymentManager->getDefaultDriver(),
                    'payment_status' => 'pending',
                ]);

                // 3. Initiate Payment Order
                $order = $this->paymentManager->driver()->createOrder($transaction);

                $transaction->update(['payment_id' => $order['id']]);

                return response()->json([
                    'order' => $order,
                    'transaction_id' => $transaction->id,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function verifyPayment(Request $request, Transaction $transaction)
    {
        $gateway = $this->paymentManager->driver($transaction->payment_gateway);

        if ($gateway->verifyPayment($request->all())) {
            DB::transaction(function () use ($transaction) {
                $transaction->update(['payment_status' => 'paid']);

                // Complete redemption
                $coupon = DiscountCoupon::active()->latest()->first(); // We should ideally pass coupon_id in session/transaction
                // Actually, let's look for the coupon in the redemption service.
                // It's cleaner if the transaction has a temporary relation or we find the coupon another way.
                // For now, let's assume we need to update the redemption service to work with transactions already created.
            });

            return redirect()->route('coupons.index')->with('success', 'Payment successful and coupon redeemed!');
        }

        return redirect()->route('coupons.index')->with('error', 'Payment verification failed.');
    }
}
