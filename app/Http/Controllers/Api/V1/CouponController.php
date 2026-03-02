<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DiscountType;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Events\CommissionEarned;
use App\Http\Controllers\Controller;
use App\Http\Requests\RedeemCouponRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Http\Resources\DiscountCouponResource;
use App\Http\Resources\TransactionResource;
use App\Models\DiscountCoupon;
use App\Models\MerchantLocation;
use App\Models\Transaction;
use App\Services\CouponRedemptionService;
use App\Services\Payments\PaymentManager;
use App\Services\StampService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @tags Coupons
 */
class CouponController extends Controller
{
    public function __construct(
        protected CouponRedemptionService $redemptionService,
        protected PaymentManager $paymentManager,
        protected StampService $stampService,
    ) {}

    /**
     * List coupons
     *
     * Returns a paginated list of active coupons. Includes eligibility info based
     * on the user's subscription plan.
     *
     * @queryParam category_id int Filter by coupon category ID.
     * @queryParam merchant_location_id int Filter by merchant location ID.
     * @queryParam per_page int Items per page (default: 12, max: 50).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $planId = $user->activeSubscription?->plan_id;

        $coupons = DiscountCoupon::query()
            ->active()
            ->when($request->input('category_id'), fn ($q, $catId) => $q->where('coupon_category_id', $catId))
            ->when($request->input('merchant_location_id'), fn ($q, $locId) => $q->where('merchant_location_id', $locId))
            ->with(['merchantLocation.merchant', 'category'])
            ->latest()
            ->paginate(min((int) $request->input('per_page', 12), 50));

        $coupons->through(function (DiscountCoupon $coupon) use ($planId) {
            $coupon->setAttribute('is_eligible', $planId ? $coupon->isEligibleForPlan($planId) : false);

            if (! $coupon->getAttribute('is_eligible')) {
                $cheapestPlan = $coupon->category?->subscriptionPlans()
                    ->orderBy('price', 'asc')
                    ->first();
                $coupon->setAttribute('required_plan', $cheapestPlan ? [
                    'name' => $cheapestPlan->name,
                    'price' => (float) $cheapestPlan->price,
                ] : null);
            }

            return $coupon;
        });

        return DiscountCouponResource::collection($coupons);
    }

    /**
     * Show coupon
     *
     * Returns detailed information about a specific coupon.
     */
    public function show(DiscountCoupon $coupon): DiscountCouponResource
    {
        $coupon->load(['merchantLocation.merchant', 'category']);

        $user = request()->user();
        $planId = $user->activeSubscription?->plan_id;
        $coupon->setAttribute('is_eligible', $planId ? $coupon->isEligibleForPlan($planId) : false);

        return new DiscountCouponResource($coupon);
    }

    /**
     * Calculate coupon redemption
     *
     * Calculates the bill breakdown (discount, fees, final amount) without creating
     * any transaction. Use this for preview before payment.
     *
     * @bodyParam bill_amount numeric required The original bill amount. Example: 500
     * @bodyParam merchant_location_id int required The merchant location ID. Example: 1
     * @bodyParam coupon_id int optional The coupon to apply. Example: 5
     *
     * @response 200 { "original_amount": 500, "discount": 50, "convenience_fee": 10, "gst": 1.8, "final_amount": 461.8, "coupon_discount": 50 }
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'bill_amount' => 'required|numeric|min:0',
            'merchant_location_id' => 'required|exists:merchant_locations,id',
            'coupon_id' => 'nullable|exists:discount_coupons,id',
        ]);

        $amount = (float) $request->input('bill_amount');

        $platformFee = config('app.platform_fee', 10);
        if (config('app.platform_fee_type') === 'percentage') {
            $platformFee = ($amount * $platformFee) / 100;
        }
        $gstAmount = round(($platformFee * config('app.gst_rate', 18)) / 100, 2);

        $discountAmount = 0.0;
        $couponDiscount = 0.0;

        if ($request->filled('coupon_id')) {
            $coupon = DiscountCoupon::active()->find($request->input('coupon_id'));
            if ($coupon) {
                if ($coupon->discount_type === DiscountType::Fixed) {
                    $discountAmount = (float) $coupon->discount_value;
                } else {
                    $discountAmount = ($amount * (float) $coupon->discount_value) / 100;
                }

                if ($coupon->max_discount_amount) {
                    $discountAmount = min($discountAmount, (float) $coupon->max_discount_amount);
                }
                $couponDiscount = round($discountAmount, 2);
            }
        }

        $finalBillAfterDiscount = max(0, $amount - $discountAmount);
        $grandTotal = round($finalBillAfterDiscount + $platformFee + $gstAmount, 2);

        return response()->json([
            'original_amount' => $amount,
            'discount' => round($discountAmount, 2),
            'convenience_fee' => round($platformFee, 2),
            'gst' => $gstAmount,
            'final_amount' => $grandTotal,
            'coupon_discount' => $couponDiscount,
        ]);
    }

    /**
     * Redeem coupon
     *
     * Initiates a coupon redemption by creating a Razorpay order. The client must
     * complete the payment using the returned order details, then call verify-payment.
     *
     * @response 200 { "order": { "id": "order_xxx" }, "transaction_id": 1 }
     * @response 422 { "error": "Payment creation failed" }
     */
    public function redeem(RedeemCouponRequest $request, DiscountCoupon $coupon): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Set primary campaign if not already set
        if (! $user->primary_campaign_id && ! empty($validated['campaign_id'])) {
            $user->update(['primary_campaign_id' => $validated['campaign_id']]);
            $user->refresh();
        }

        $merchantLocation = MerchantLocation::with('merchant')->findOrFail($validated['merchant_location_id']);
        $amount = (float) $validated['amount'];

        try {
            return DB::transaction(function () use ($user, $coupon, $merchantLocation, $amount): JsonResponse {
                $platformFee = config('app.platform_fee', 10);
                if (config('app.platform_fee_type') === 'percentage') {
                    $platformFee = ($amount * $platformFee) / 100;
                }
                $gstAmount = ($platformFee * config('app.gst_rate', 18)) / 100;

                $discountAmount = 0.0;
                if ($coupon->discount_type === DiscountType::Fixed) {
                    $discountAmount = (float) $coupon->discount_value;
                } else {
                    $discountAmount = ($amount * (float) $coupon->discount_value) / 100;
                }

                if ($coupon->max_discount_amount) {
                    $discountAmount = min($discountAmount, (float) $coupon->max_discount_amount);
                }

                $finalBillAfterDiscount = max(0, $amount - $discountAmount);
                $grandTotal = $finalBillAfterDiscount + $platformFee + $gstAmount;
                $commissionAmount = ($finalBillAfterDiscount * $merchantLocation->commission_percentage) / 100;

                $idempotencyKey = 'coupon_'.$user->id.'_'.$coupon->id.'_'.Str::uuid();

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'coupon_id' => $coupon->id,
                    'merchant_location_id' => $merchantLocation->id,
                    'original_bill_amount' => $amount,
                    'discount_amount' => $discountAmount,
                    'amount' => $finalBillAfterDiscount,
                    'platform_fee' => $platformFee,
                    'gst_amount' => $gstAmount,
                    'total_amount' => $grandTotal,
                    'payment_gateway' => $this->paymentManager->getDefaultDriver(),
                    'payment_status' => PaymentStatus::Pending,
                    'type' => TransactionType::CouponRedemption,
                    'idempotency_key' => $idempotencyKey,
                    'commission_amount' => $commissionAmount,
                ]);

                $order = $this->paymentManager->driver()->createOrder($transaction);
                $transaction->update(['razorpay_order_id' => $order['id']]);

                if (! empty($order['transfers'][0]['id'])) {
                    $transaction->update(['transfer_id' => $order['transfers'][0]['id']]);
                }

                return response()->json([
                    'order' => $order,
                    'transaction_id' => $transaction->id,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Verify coupon payment
     *
     * Verifies the Razorpay payment for a coupon redemption. On success, the coupon
     * is redeemed and stamps are awarded.
     *
     * @response 200 { "message": "Payment verified and coupon redeemed successfully.", "transaction": {} }
     * @response 422 { "error": "Payment verification failed." }
     */
    public function verifyPayment(VerifyPaymentRequest $request): JsonResponse
    {
        $transaction = Transaction::where('razorpay_order_id', $request->input('razorpay_order_id'))
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $gateway = $this->paymentManager->driver($transaction->payment_gateway);

        if (! $gateway->verifyPayment($request->all())) {
            return response()->json(['error' => 'Payment verification failed.'], 422);
        }

        DB::transaction(function () use ($transaction, $request): void {
            $transaction->update([
                'payment_status' => PaymentStatus::Paid,
                'payment_id' => $request->input('razorpay_payment_id'),
            ]);

            $user = $transaction->user;

            if ($transaction->coupon_id) {
                $coupon = DiscountCoupon::find($transaction->coupon_id);
                if ($coupon) {
                    $this->redemptionService->redeemCoupon($user, $coupon, $transaction, [
                        'original_bill_amount' => (float) $transaction->original_bill_amount,
                        'discount_amount' => (float) $transaction->discount_amount,
                        'platform_fee' => (float) $transaction->platform_fee,
                        'gst_amount' => (float) $transaction->gst_amount,
                        'total_paid' => (float) $transaction->total_amount,
                    ]);
                }
            }

            $this->stampService->awardStampsForCouponRedemption($transaction);

            if ($transaction->commission_amount > 0 && $user->primary_campaign_id) {
                $campaign = $user->primaryCampaign;
                if ($campaign) {
                    CommissionEarned::dispatch($campaign, (float) $transaction->commission_amount);
                }
            }
        });

        return response()->json([
            'message' => 'Payment verified and coupon redeemed successfully.',
            'transaction' => new TransactionResource($transaction->load(['coupon', 'merchantLocation', 'stamps'])),
        ]);
    }
}
