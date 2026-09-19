<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateEvent;
use App\Models\AffiliateProduct;
use App\Models\Brand;
use App\Models\AffiliateNetwork;
use App\Support\AdminPageSize;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateAnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AffiliateProduct::query()
            ->with('brand:id,name')
            ->withCount([
                'events as impressions_count' => fn ($events) => $events->where('kind', 'impression'),
                'events as clicks_count' => fn ($events) => $events->where('kind', 'click'),
            ])->orderByDesc('impressions_count')->orderBy('id');
        $page = $query->paginate(AdminPageSize::resolve($request, $query, 20));
        $page->getCollection()->each(function ($product) {
            $product->setAttribute('ctr', $product->impressions_count > 0
                ? round(100 * $product->clicks_count / $product->impressions_count, 2) : 0);
        });
        $brands = Brand::query()->withCount([
            'affiliateEvents as impressions_count' => fn ($events) => $events->where('kind', 'impression'),
            'affiliateEvents as clicks_count' => fn ($events) => $events->where('kind', 'click'),
        ])->orderBy('name')->get(['id', 'name']);
        $networks = AffiliateNetwork::query()->withCount([
            'affiliateEvents as impressions_count' => fn ($events) => $events->where('kind', 'impression'),
            'affiliateEvents as clicks_count' => fn ($events) => $events->where('kind', 'click'),
        ])->orderBy('name')->get(['id', 'name']);
        $placements = AffiliateEvent::query()->selectRaw('placement, kind, COUNT(*) as total')
            ->groupBy('placement', 'kind')->get()->groupBy(fn ($event) => $event->placement ?? 'unknown')
            ->map(fn ($events, $placement) => [
                'placement' => $placement,
                'impressions' => (int) ($events->firstWhere('kind', 'impression')?->total ?? 0),
                'clicks' => (int) ($events->firstWhere('kind', 'click')?->total ?? 0),
            ])->values();
        $daily = AffiliateEvent::query()->selectRaw('DATE(created_at) as date, kind, COUNT(*) as total')
            ->groupByRaw('DATE(created_at), kind')->orderBy('date')->get();
        return ApiResponse::success([
            'products' => $page->items(),
            'brands' => $brands,
            'networks' => $networks,
            'placements' => $placements,
            'daily' => $daily,
            'totals' => [
                'impressions' => AffiliateEvent::where('kind', 'impression')->count(),
                'clicks' => AffiliateEvent::where('kind', 'click')->count(),
            ],
            'pagination' => [
                'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(), 'total' => $page->total(),
            ],
        ], 'Affiliate analytics retrieved successfully.');
    }
}
