<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\ApiResponse;
use App\Services\CacheService;

use App\Http\Resources\Api\AffiliateProductResource;
use App\Http\Resources\Api\PostResource;
use App\Http\Resources\Api\Collections\PaginatedApiCollection;
use Illuminate\Support\Facades\Cache;

class AffiliateProductController extends Controller
{
    /**
     * List published/active affiliate products.
     */
    public function index(Request $request)
    {
        $perPage = min(
            max($request->integer('per_page', 10), 1),
            50
        );

        /*
        |--------------------------------------------------------------------------
        | Cache Key
        |--------------------------------------------------------------------------
        */

        $cacheKey = 'public_products_' . md5(
            json_encode($request->query())
        );

        /*
        |--------------------------------------------------------------------------
        | Get Products (Cached)
        |--------------------------------------------------------------------------
        */

        $products = Cache::remember(
            CacheService::publicProductsKey(),
            now()->addHours(6),
            function () use ($perPage) {

                return AffiliateProduct::query()
                    ->where('status', true)
                    ->with([
                        'brand:id,name,slug,logo',
                        'category:id,name,slug',
                        'seoMeta:id,seoable_id,seoable_type,meta_title,meta_description,canonical_url',
                    ])
                    ->latest()
                    ->paginate($perPage);
            }
        );

        return AffiliateProductResource::collection($products)
            ->additional([
                'success' => true,
        ]);
    }

    /**
     * Featured affiliate products.
     */
    public function featured(): JsonResponse
    {
        $products = Cache::remember(
            CacheService::featuredProductsKey(),
            now()->addHours(6),
            function () {

                return AffiliateProduct::query()
                    ->where('status', true)
                    ->where('featured', true)
                    ->with([
                        'brand',
                        'category',

                        'seoMeta' => function ($query) {
                            $query->with('seoable');
                        },
                    ])
                    ->orderByDesc('rating')
                    ->orderByDesc('updated_at')
                    ->limit(8)
                    ->get();
            }
        );

        return ApiResponse::success(
            AffiliateProductResource::collection($products),
            'Featured products retrieved successfully.'
        );
    }


    /**
     * Show a single affiliate product.
     */
    public function show(string $slug): JsonResponse
    {
        $product = Cache::remember(
            CacheService::publicProductKey($slug),
            now()->addHours(6),
            function () use ($slug) {

                return AffiliateProduct::query()
                    ->where('slug', $slug)
                    ->where('status', true)
                    ->with([
                        'brand',
                        'category',

                        'seoMeta' => function ($query) {
                            $query->with('seoable');
                        },
                    ])
                    ->firstOrFail();
            }
        );

        return ApiResponse::success(
            [
                'product' => new AffiliateProductResource($product),
            ],
            'Affiliate product retrieved successfully.'
        );
    }
}