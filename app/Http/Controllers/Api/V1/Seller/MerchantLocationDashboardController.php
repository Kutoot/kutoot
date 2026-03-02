<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\MerchantLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantLocationDashboardController extends Controller
{
    /**
     * Dashboard summary with KPIs for the merchant location.
     */
    public function summary(Request $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $transactions = $merchantLocation->transactions()
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $successTxns = $transactions->filter(
            fn ($t) => in_array($t->payment_status, [PaymentStatus::Paid, PaymentStatus::Completed])
        );

        $failedTxns = $transactions->filter(
            fn ($t) => $t->payment_status === PaymentStatus::Failed
        );

        $totalSales = $successTxns->sum('original_bill_amount');
        $totalDiscount = $successTxns->sum('discount_amount');
        $totalCommissionPaid = $successTxns->sum('commission_amount');

        // Unique visitors (distinct users who transacted)
        $totalVisitors = $successTxns->pluck('user_id')->unique()->count();

        // Redeemed visitors — txns with a coupon_id
        $redeemedVisitors = $successTxns->filter(fn ($t) => $t->coupon_id !== null)->pluck('user_id')->unique()->count();

        // Repeat visitors — users who appear more than once
        $userCounts = $successTxns->groupBy('user_id');
        $repeatVisitors = $userCounts->filter(fn ($group) => $group->count() > 1)->count();

        // Offer views (estimated from visitors)
        $offerViews = max((int) ($totalVisitors * 2.5), $totalVisitors);

        // Active deals/campaigns
        $activeCampaigns = $merchantLocation->coupons()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })->count();

        // Commission percent from model
        $commissionPercent = (float) $merchantLocation->commission_percentage;

        // Seller balance (net amount from monthly summaries for current month, or derived)
        $currentMonthSummary = $merchantLocation->monthlySummaries()
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();

        $sellerBalance = $currentMonthSummary
            ? (float) $currentMonthSummary->net_amount
            : (float) ($totalSales - $totalDiscount - $totalCommissionPaid);

        // Peak days analysis
        $dayOfWeekCounts = $successTxns->groupBy(fn ($t) => $t->created_at->format('D'))->map->count()->sortDesc();
        $peakDays = $dayOfWeekCounts->keys()->take(3)->values()->toArray();

        // Peak hours analysis
        $hourCounts = $successTxns->groupBy(fn ($t) => $t->created_at->format('gA'))->map->count()->sortDesc();
        $topHours = $hourCounts->keys()->take(4)->values()->toArray();
        $peakHours = [];
        for ($i = 0; $i < count($topHours); $i += 2) {
            if (isset($topHours[$i + 1])) {
                $peakHours[] = $topHours[$i].'-'.$topHours[$i + 1];
            } elseif (isset($topHours[$i])) {
                $peakHours[] = $topHours[$i];
            }
        }

        if (empty($peakDays)) {
            $peakDays = ['Sat', 'Sun', 'Fri'];
        }
        if (empty($peakHours)) {
            $peakHours = ['12PM-2PM', '6PM-8PM'];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'totalSales' => round($totalSales, 2),
                'totalDiscount' => round($totalDiscount, 2),
                'totalVisitors' => $totalVisitors,
                'offerViews' => $offerViews,
                'redeemedVisitors' => $redeemedVisitors,
                'repeatVisitors' => $repeatVisitors,
                'activeCampaigns' => $activeCampaigns,
                'commissionPercent' => $commissionPercent,
                'sellerBalance' => round($sellerBalance, 2),
                'successTransactions' => $successTxns->count(),
                'failedTransactions' => $failedTxns->count(),
                'totalCommissionPaid' => round($totalCommissionPaid, 2),
                'peakDays' => $peakDays,
                'peakHours' => $peakHours,
            ],
        ]);
    }

    /**
     * Revenue trend over the last N days.
     */
    public function revenueTrend(Request $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $days = (int) $request->input('days', 7);
        $days = min(max($days, 1), 90);

        $from = now()->subDays($days)->startOfDay();

        $transactions = $merchantLocation->transactions()
            ->where('created_at', '>=', $from)
            ->whereIn('payment_status', [PaymentStatus::Paid, PaymentStatus::Completed])
            ->selectRaw('DATE(created_at) as date, SUM(original_bill_amount) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with zero
        $trend = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $found = $transactions->firstWhere('date', $date);
            $trend[] = [
                'date' => $date,
                'amount' => $found ? round((float) $found->amount, 2) : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'trend' => $trend,
            ],
        ]);
    }

    /**
     * Visitor trend over the last N days.
     */
    public function visitorsTrend(Request $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $days = (int) $request->input('days', 7);
        $days = min(max($days, 1), 90);

        $from = now()->subDays($days)->startOfDay();

        $transactions = $merchantLocation->transactions()
            ->where('created_at', '>=', $from)
            ->whereIn('payment_status', [PaymentStatus::Paid, PaymentStatus::Completed])
            ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT user_id) as visitors')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with zero
        $trend = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $found = $transactions->firstWhere('date', $date);
            $trend[] = [
                'date' => $date,
                'visitors' => $found ? (int) $found->visitors : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'trend' => $trend,
            ],
        ]);
    }
}
