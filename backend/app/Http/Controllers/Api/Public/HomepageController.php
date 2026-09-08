<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

use App\Models\Post;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AffiliateProduct;

use App\Http\Resources\Api\PostResource;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\BrandResource;
use App\Http\Resources\Api\AffiliateProductResource;

use App\Services\CacheService;
use App\Support\ApiResponse;

class HomepageController extends Controller
{
    /**
     * Homepage API.
     */
    public function index(): JsonResponse
    {
        $homepage = Cache::remember(
            CacheService::homepageKey(),
            now()->addHours(6),
            function () {

                /*
                |--------------------------------------------------------------------------
                | Hero Featured Products
                |--------------------------------------------------------------------------
                */

                $heroProducts = AffiliateProduct::query()
                    ->where('status', true)
                    ->where('featured', true)
                    ->with([
                        'brand',
                        'category',
                        'seoMeta.seoable',
                    ])
                    ->orderByDesc('rating')
                    ->limit(4)
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Popular Posts
                |--------------------------------------------------------------------------
                */

                $popularPosts = Post::query()
                    ->where('status', 'published')
                    ->with([
                        'category',
                        'tags',
                        'seoMeta.seoable',
                    ])
                    ->orderByDesc('views')
                    ->orderByDesc('published_at')
                    ->limit(6)
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Latest Posts
                |--------------------------------------------------------------------------
                */

                $latestPosts = Post::query()
                    ->where('status', 'published')
                    ->with([
                        'category',
                        'tags',
                        'seoMeta.seoable',
                    ])
                    ->latest('published_at')
                    ->limit(6)
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Featured Categories
                |--------------------------------------------------------------------------
                */

                $categories = Category::query()
                    ->withCount('posts')
                    ->orderByDesc('posts_count')
                    ->limit(6)
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Featured Brands
                |--------------------------------------------------------------------------
                */

                $brands = Brand::query()
                    ->where('status', true)
                    ->withCount('affiliateProducts')
                    ->orderByDesc('affiliate_products_count')
                    ->limit(8)
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Statistics
                |--------------------------------------------------------------------------
                */

                $statistics = [
                    'posts' => Post::where('status', 'published')->count(),
                    'products' => AffiliateProduct::where('status', true)->count(),
                    'categories' => Category::count(),
                    'brands' => Brand::where('status', true)->count(),
                ];

                return [
                    'hero_products' => AffiliateProductResource::collection($heroProducts),

                    'popular_posts' => PostResource::collection($popularPosts),

                    'latest_posts' => PostResource::collection($latestPosts),

                    'featured_categories' => CategoryResource::collection($categories),

                    'featured_brands' => BrandResource::collection($brands),

                    'statistics' => $statistics,
                ];
            }
        );

        return ApiResponse::success(
            $homepage,
            'Homepage data retrieved successfully.'
        );
    }
}