<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / Transactions
 */
class TransactionController extends Controller
{
    /**
     * List all transactions.
     *
     * @queryParam filter[user_id] int Filter by user.
     * @queryParam filter[type] string Filter by type (CouponRedemption, PlanPurchase, StampPurchase).
     * @queryParam filter[payment_status] string Filter by payment status.
     * @queryParam filter[merchant_location_id] int Filter by merchant location.
     * @queryParam filter[date_from] date Filter from date.
     * @queryParam filter[date_to] date Filter to date.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Transaction::class);

        $transactions = Transaction::query()
            ->with(['user', 'coupon', 'merchantLocation.merchant', 'stamps'])
            ->when($request->input('filter.user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->input('filter.type'), fn ($q, $t) => $q->where('type', $t))
            ->when($request->input('filter.payment_status'), fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->input('filter.merchant_location_id'), fn ($q, $id) => $q->where('merchant_location_id', $id))
            ->when($request->input('filter.date_from'), fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->input('filter.date_to'), fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return TransactionResource::collection($transactions);
    }

    /**
     * Show a transaction.
     */
    public function show(Transaction $transaction): TransactionResource
    {
        $this->authorize('view', $transaction);

        $transaction->load(['user', 'coupon', 'merchantLocation.merchant', 'stamps', 'couponRedemption']);

        return new TransactionResource($transaction);
    }
}
