<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Fetch subscription/plan purchase transactions
        $subscriptionTransactions = $user->transactions()
            ->where('type', TransactionType::PlanPurchase)
            ->latest()
            ->paginate(20, ['*'], 'sub_page');

        // Pre-load plan names for subscription transactions via idempotency key
        $planIds = $subscriptionTransactions
            ->map(function ($t) {
                // idempotency_key format: plan_{userId}_{planId}_{uuid}
                if ($t->idempotency_key && preg_match('/^plan_\d+_(\d+)_/', $t->idempotency_key, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values();

        $planNames = $planIds->isNotEmpty()
            ? SubscriptionPlan::whereIn('id', $planIds)->pluck('name', 'id')
            : collect();

        // Fetch coupon redemption transactions
        $couponTransactions = $user->transactions()
            ->where('type', TransactionType::CouponRedemption)
            ->with(['coupon:id,title', 'merchantLocation:id,branch_name', 'couponRedemption'])
            ->latest()
            ->paginate(20, ['*'], 'coupon_page');

        $subscriptionData = $subscriptionTransactions->map(function ($t) use ($planNames) {
            $planName = 'N/A';
            if ($t->idempotency_key && preg_match('/^plan_\d+_(\d+)_/', $t->idempotency_key, $matches)) {
                $planName = $planNames[(int) $matches[1]] ?? 'N/A';
            }

            return [
                'id' => $t->id,
                'type' => 'subscription',
                'plan_name' => $planName,
                'amount' => (float) $t->original_bill_amount,
                'gst_amount' => (float) ($t->gst_amount ?? 0),
                'total_amount' => (float) $t->total_amount,
                'payment_status' => $t->payment_status->getLabel(),
                'payment_method' => $t->payment_gateway,
                'payment_id' => $t->payment_id,
                'created_at' => $t->created_at->format('M d, Y H:i'),
                'created_at_human' => $t->created_at->diffForHumans(),
            ];
        });

        $couponData = $couponTransactions->map(fn ($t) => [
            'id' => $t->id,
            'type' => 'coupon',
            'coupon_title' => $t->coupon?->title ?? 'N/A',
            'merchant_location' => $t->merchantLocation?->branch_name ?? 'N/A',
            'bill_amount' => (float) $t->original_bill_amount,
            'discount_applied' => (float) ($t->couponRedemption?->discount_applied ?? $t->discount_amount ?? 0),
            'platform_fee' => (float) ($t->couponRedemption?->platform_fee ?? 0),
            'gst_amount' => (float) ($t->couponRedemption?->gst_amount ?? 0),
            'total_paid' => (float) ($t->couponRedemption?->total_paid ?? $t->total_amount),
            'payment_status' => $t->payment_status->getLabel(),
            'payment_method' => $t->payment_gateway,
            'payment_id' => $t->payment_id,
            'created_at' => $t->created_at->format('M d, Y H:i'),
            'created_at_human' => $t->created_at->diffForHumans(),
        ]);

        return Inertia::render('Transactions/Index', [
            'subscriptionTransactions' => [
                'data' => $subscriptionData,
                'links' => $subscriptionTransactions->render(),
                'total' => $subscriptionTransactions->total(),
            ],
            'couponTransactions' => [
                'data' => $couponData,
                'links' => $couponTransactions->render(),
                'total' => $couponTransactions->total(),
            ],
        ]);
    }
}
