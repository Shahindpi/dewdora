<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Http\Requests\StoreAffiliateProductRequest;
use App\Http\Requests\UpdateAffiliateProductRequest;
use App\Services\CacheService;



class AffiliateProductController extends Controller
{
    /**
     * List affiliate products.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AffiliateProduct::query()
            ->with([
                'brand',
                'affiliateNetwork',
                'category',
            ])
            ->withCount('posts')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where(
                    'name',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'short_description',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->boolean('status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Featured
        |--------------------------------------------------------------------------
        */

        if ($request->filled('featured')) {
            $query->where(
                'featured',
                $request->boolean('featured')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->integer('category_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max(
                (int) $request->input('per_page', 15),
                1
            ),
            100
        );

        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }


    /**
     * Create affiliate product.
     */
    public function store(
        StoreAffiliateProductRequest $request
    ): JsonResponse {

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Generate Slug
        |--------------------------------------------------------------------------
        */

        $validated['slug'] =
            $validated['slug']
            ?? Str::slug($validated['name']);

        /*
        |--------------------------------------------------------------------------
        | Defaults
        |--------------------------------------------------------------------------
        */

        $validated['currency'] =
            $validated['currency'] ?? 'USD';

        $validated['free_trial'] =
            $validated['free_trial'] ?? false;

        $validated['featured'] =
            $validated['featured'] ?? false;

        $validated['status'] =
            $validated['status'] ?? true;

        /*
        |--------------------------------------------------------------------------
        | Create Product
        |--------------------------------------------------------------------------
        */

        $product = AffiliateProduct::create($validated);

        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $product->load([
            'brand',
            'affiliateNetwork',
            'category',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear Cache
        |--------------------------------------------------------------------------
        */

        CacheService::clearProduct($product->slug);
        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Affiliate product created successfully.',
            'data' => $product,
        ], 201);
    }


    /**
     * Show affiliate product.
     */
    public function show(
        AffiliateProduct $affiliateProduct
    ): JsonResponse {

        $affiliateProduct->load([
            'brand',
            'affiliateNetwork',
            'category',
            'posts',
            'seoMeta',
        ]);

        return response()->json([
            'success' => true,
            'data' => $affiliateProduct,
        ]);
    }


    /**
     * Update affiliate product.
     */
    public function update(
        UpdateAffiliateProductRequest $request,
        AffiliateProduct $affiliateProduct
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Store Previous Slug
        |--------------------------------------------------------------------------
        */

        $oldSlug = $affiliateProduct->slug;

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Generate Slug
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['name']) &&
            !isset($validated['slug'])
        ) {
            $validated['slug'] =
                Str::slug($validated['name']);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Product
        |--------------------------------------------------------------------------
        */

        $affiliateProduct->update($validated);

        /*
        |--------------------------------------------------------------------------
        | Refresh Model
        |--------------------------------------------------------------------------
        */

        $affiliateProduct->refresh();

        $affiliateProduct->load([
            'brand',
            'affiliateNetwork',
            'category',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear Cache
        |--------------------------------------------------------------------------
        */

        CacheService::clearProduct($oldSlug);
        CacheService::clearProduct($affiliateProduct->slug);

        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Affiliate product updated successfully.',
            'data' => $affiliateProduct,
        ]);
    }

    /**
     * Delete affiliate product.
     */
    public function destroy(
        AffiliateProduct $affiliateProduct
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Clear Cache Before Delete
        |--------------------------------------------------------------------------
        */

        CacheService::clearProduct($affiliateProduct->slug);

        $affiliateProduct->delete();

        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Affiliate product deleted successfully.',
        ]);
    }
}