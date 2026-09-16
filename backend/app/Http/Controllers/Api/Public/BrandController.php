<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

use App\Http\Resources\Api\BrandResource;
use App\Http\Resources\Api\AffiliateProductResource;
use App\Support\ApiResponse;
use App\Services\CacheService;

class BrandController extends Controller
{
    /**
     * List public brands.
     */
    public function index(Request $request)
    {
        $perPage = min(
            max($request->integer('per_page', 10), 1),
            50
        );

        $cacheKey = CacheService::publicBrandsKey(
            $request->query(),
            $perPage
        );

        return CacheService::remember(
            $cacheKey,
            function () use ($request, $perPage) {

                $brands = Brand::query()
                    ->where('status', true)
                    ->withCount('affiliateProducts')

                    ->when(
                        $request->filled('search'),
                        function ($query) use ($request) {

                            $search = $request->string('search');

                            $query->where(function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                  ->orWhere(
                                      'description',
                                      'like',
                                      "%{$search}%"
                                  );
                            });
                        }
                    )

                    ->orderBy('name')
                    ->paginate($perPage);

                return ApiResponse::paginated(
                    BrandResource::collection($brands),
                    'Brands retrieved successfully.'
                );
            }
        );
    }

    /**
     * Show a single public brand.
     */
    public function show(string $slug)
    {
        $cacheKey = CacheService::publicBrandKey($slug);

        return CacheService::remember(
            $cacheKey,
            function () use ($slug) {

                $brand = Brand::query()
                    ->where('status', true)
                    ->withCount(['affiliateProducts' => fn ($query) => $query->where('status', true)])
                    ->where('slug', $slug)
                    ->firstOrFail();

                $products = $brand->affiliateProducts()
                    ->where('status', true)
                    ->with(['brand', 'affiliateNetwork', 'category', 'seoMeta'])
                    ->orderByDesc('featured')
                    ->latest()
                    ->paginate(12);

                return ApiResponse::success(
                    [
                        'brand' => new BrandResource($brand),
                        'products' => AffiliateProductResource::collection($products),
                    ],
                    'Brand retrieved successfully.'
                );
            }
        );
    }
}
