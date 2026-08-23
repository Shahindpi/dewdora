<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\ApiResponse;

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
            $cacheKey,
            now()->addMinutes(20),
            function () use ($request, $perPage) {

                return AffiliateProduct::query()
                    ->with([
                        'brand',
                        'category',
                        'seoMeta',
                    ])

                    ->where('status', true)

                    // Category filter
                    ->when(
                        $request->filled('category'),
                        function ($query) use ($request) {
                            $query->whereHas(
                                'category',
                                function ($categoryQuery) use ($request) {
                                    $categoryQuery->where(
                                        'slug',
                                        $request->string('category')
                                    );
                                }
                            );
                        }
                    )

                    // Brand filter
                    ->when(
                        $request->filled('brand'),
                        function ($query) use ($request) {
                            $query->whereHas(
                                'brand',
                                function ($brandQuery) use ($request) {
                                    $brandQuery->where(
                                        'slug',
                                        $request->string('brand')
                                    );
                                }
                            );
                        }
                    )

                    // Featured filter
                    ->when(
                        $request->boolean('featured'),
                        fn ($query) => $query->where('featured', true)
                    )

                    // Search
                    ->when(
                        $request->filled('search'),
                        function ($query) use ($request) {
                            $search = $request->string('search');

                            $query->where(function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                    ->orWhere(
                                        'short_description',
                                        'like',
                                        "%{$search}%"
                                    );
                            });
                        }
                    )

                    ->orderByDesc('featured')
                    ->orderBy('name')

                    ->paginate($perPage);
            }
        );

        return AffiliateProductResource::collection($products)
            ->additional([
                'success' => true,
        ]);
    }



    /**
     * Show a single affiliate product.
     */
    public function show(string $slug)
    {
        /*
        |--------------------------------------------------------------------------
        | Find Product (Cached)
        |--------------------------------------------------------------------------
        */

        $product = Cache::remember(
            "public_product_{$slug}",
            now()->addMinutes(20),
            function () use ($slug) {

                return AffiliateProduct::query()
                    ->with([
                        'brand',
                        'category',
                        'seoMeta',
                    ])
                    ->where('slug', $slug)
                    ->where('status', true)
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