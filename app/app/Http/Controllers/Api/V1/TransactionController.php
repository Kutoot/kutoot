<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Transactions
 */
class TransactionController extends Controller
{
    /**
     * List transactions
     *
     * Returns the authenticated user's transactions with optional filters.
     *
     * @queryParam type string Filter by type: coupon_redemption, plan_purchase.
     * @queryParam status string Filter by payment status: pending, paid, completed, refunded, failed.
     * @queryParam from string Filter by start date (Y-m-d).
     * @queryParam to string Filter by end date (Y-m-d).
     * @queryParam per_page int Items per page (default: 15, max: 50).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = $request->user()->transactions()
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->input('status'), fn ($q, $status) => $q->where('payment_status', $status))
            ->when($request->input('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->input('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->with(['coupon', 'merchantLocation.merchant'])
            ->latest()
            ->paginate(min((int) $request->input('per_page', 15), 50));

        return TransactionResource::collection($transactions);
    }

    /**
     * Show transaction
     *
     * Returns detailed information about a specific transaction owned by the authenticated user.
     *
     * @response 404 { "message": "Not found." }
     */
    public function show(Request $request, Transaction $transaction): TransactionResource
    {
        // Ensure the transaction belongs to the authenticated user
        if ($transaction->user_id !== $request->user()->id) {
            abort(403, 'This transaction does not belong to you.');
        }

        $transaction->load(['coupon', 'merchantLocation.merchant', 'stamps', 'couponRedemption']);

        return new TransactionResource($transaction);
    }
}
