<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],

            'affiliate_network_id' => [
                'nullable',
                'integer',
                'exists:affiliate_networks,id',
            ],

            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:affiliate_products,slug',
            ],

            'short_description' => [
                'nullable',
                'string',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'website_url' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'affiliate_url' => [
                'required',
                'url',
                'max:2048',
            ],

            'price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'nullable',
                'string',
                'max:10',
            ],

            'commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'free_trial' => [
                'nullable',
                'boolean',
            ],

            'rating' => [
                'nullable',
                'numeric',
                'min:0',
                'max:5',
            ],

            'featured_image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'pros' => [
                'nullable',
                'array',
            ],

            'cons' => [
                'nullable',
                'array',
            ],

            'featured' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

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
        | Create
        |--------------------------------------------------------------------------
        */

        $product = AffiliateProduct::create(
            $validated
        );

        /*
        |--------------------------------------------------------------------------
        | Load relationships
        |--------------------------------------------------------------------------
        */

        $product->load([
            'brand',
            'affiliateNetwork',
            'category',
        ]);

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
        Request $request,
        AffiliateProduct $affiliateProduct
    ): JsonResponse {

        $validated = $request->validate([
            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],

            'affiliate_network_id' => [
                'nullable',
                'integer',
                'exists:affiliate_networks,id',
            ],

            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'unique:affiliate_products,slug,'
                    . $affiliateProduct->id,
            ],

            'short_description' => [
                'nullable',
                'string',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'website_url' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'affiliate_url' => [
                'sometimes',
                'required',
                'url',
                'max:2048',
            ],

            'price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'nullable',
                'string',
                'max:10',
            ],

            'commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'free_trial' => [
                'nullable',
                'boolean',
            ],

            'rating' => [
                'nullable',
                'numeric',
                'min:0',
                'max:5',
            ],

            'featured_image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'pros' => [
                'nullable',
                'array',
            ],

            'cons' => [
                'nullable',
                'array',
            ],

            'featured' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Slug
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['name']) &&
            !isset($validated['slug'])
        ) {
            $validated['slug'] =
                Str::slug($validated['name']);
        }

        $affiliateProduct->update(
            $validated
        );

        $affiliateProduct->refresh();

        $affiliateProduct->load([
            'brand',
            'affiliateNetwork',
            'category',
        ]);

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

        $affiliateProduct->delete();

        return response()->json([
            'success' => true,
            'message' => 'Affiliate product deleted successfully.',
        ]);
    }
}