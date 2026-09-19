<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateEvent;
use App\Models\AffiliateProduct;
use App\Models\Brand;
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
        $brands = Brand::query()->withCount([
            'affiliateEvents as impressions_count' => fn ($events) => $events->where('kind', 'impression'),
            'affiliateEvents as clicks_count' => fn ($events) => $events->where('kind', 'click'),
        ])->orderBy('name')->get(['id', 'name']);
        return ApiResponse::success([
            'products' => $page->items(),
            'brands' => $brands,
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
