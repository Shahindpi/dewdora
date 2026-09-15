<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\AiTool;
use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use App\Support\ApiResponse;

class AnalyticsSummaryController extends Controller
{
    /**
     * Click/event totals by type over a date range, plus top-clicked
     * affiliate products and AI tools.
     */
    public function index(Request $request)
    {
        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', now()->toDateString());

        $totals = AnalyticsEvent::query()
            ->between($from, $to)
            ->selectRaw('event_type, count(*) as total')
            ->groupBy('event_type')
            ->pluck('total', 'event_type');

        $topProducts = AffiliateProduct::query()
            ->orderByDesc('click_count')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'click_count']);

        $topTools = AiTool::query()
            ->orderByDesc('click_count')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'click_count']);

        return ApiResponse::success([
            'range' => ['from' => $from, 'to' => $to],
            'totals_by_event_type' => $totals,
            'top_affiliate_products' => $topProducts,
            'top_ai_tools' => $topTools,
        ]);
    }
}
