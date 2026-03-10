<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\MerchantLocation;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantLocationVisitorController extends Controller
{
    /**
     * List visitors (customers) who transacted at this merchant location.
     */
    public function index(Request $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $limit = min((int) ($request->input('limit', 20)), 100);
        $page = max((int) ($request->input('page', 1)), 1);
        $search = $request->input('search');

        // Get distinct users who have transactions at this location in the date range
        $query = Transaction::query()
            ->where('merchant_location_id', $merchantLocation->id)
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('payment_status', [PaymentStatus::Paid, PaymentStatus::Completed])
            ->with('user');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get all matching transactions, then group by user
        $allTransactions = $query->orderByDesc('created_at')->get();

        $grouped = $allTransactions->groupBy('user_id');
        $total = $grouped->count();

        // Paginate the grouped results
        $pagedGroups = $grouped->slice(($page - 1) * $limit, $limit);

        $rows = $pagedGroups->map(function ($txns, $userId) {
            $user = $txns->first()->user;
            $latestTxn = $txns->first(); // already sorted descending
            $redeemedCount = $txns->filter(fn ($t) => $t->coupon_id !== null)->count();

            return [
                'visitorId' => $userId,
                'name' => $user?->name ?? 'Unknown',
                'phone' => $user?->mobile ?? '',
                'email' => $user?->email ?? '',
                'visitedOn' => $latestTxn->created_at->toISOString(),
                'totalVisits' => $txns->count(),
                'redeemed' => $redeemedCount > 0,
                'redeemedCount' => $redeemedCount,
                'transaction' => [
                    'txnId' => $latestTxn->payment_id ?? (string) $latestTxn->id,
                    'totalAmount' => (float) $latestTxn->total_amount,
                ],
                'totalSpent' => round($txns->sum('total_amount'), 2),
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $rows,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
            ],
        ]);
    }
}
