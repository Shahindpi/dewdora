<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Admin dashboard statistics.
     */
    public function index(): JsonResponse
    {
        $data = Cache::remember(
            'admin_dashboard_overview',
            now()->addMinutes(5),
            function () {

                return [

                    /*
                    |--------------------------------------------------------------------------
                    | Overview Cards
                    |--------------------------------------------------------------------------
                    */

                    'overview' => [
                        'posts' => Post::count(),

                        'published_posts' => Post::where(
                            'status',
                            'published'
                        )->count(),

                        'draft_posts' => Post::where(
                            'status',
                            'draft'
                        )->count(),

                        'categories' => Category::count(),

                        'tags' => Tag::count(),

                        'affiliate_products' => AffiliateProduct::count(),

                        'active_products' => AffiliateProduct::where(
                            'status',
                            true
                        )->count(),

                        'users' => User::count(),
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | Recent Posts
                    |--------------------------------------------------------------------------
                    */

                    'recent_posts' => Post::query()
                        ->select([
                            'id',
                            'title',
                            'slug',
                            'status',
                            'published_at',
                            'created_at',
                        ])
                        ->latest()
                        ->limit(5)
                        ->get(),

                    /*
                    |--------------------------------------------------------------------------
                    | Top Categories
                    |--------------------------------------------------------------------------
                    */

                    'top_categories' => Category::query()
                        ->withCount('posts')
                        ->orderByDesc('posts_count')
                        ->limit(5)
                        ->get([
                            'id',
                            'name',
                            'slug',
                        ]),
                ];
            }
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
    /**
     * Dashboard analytics.
     */
    public function analytics(): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Posts published during the last 12 months
        |--------------------------------------------------------------------------
        */

        $monthlyPosts = Post::query()
            ->selectRaw("
                DATE_FORMAT(published_at, '%Y-%m') as month,
                COUNT(*) as total
            ")
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $postsPerMonth = collect(range(0, 11))->map(function ($i) use ($monthlyPosts) {
            $month = now()->subMonths(11 - $i)->format('Y-m');

            return [
                'month' => $month,
                'posts' => (int) ($monthlyPosts[$month] ?? 0),
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Posts by status
        |--------------------------------------------------------------------------
        */

        $postStatus = Post::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Products grouped by category
        |--------------------------------------------------------------------------
        */

        $productsByCategory = Category::query()
            ->withCount('affiliateProducts')
            ->orderByDesc('affiliate_products_count')
            ->limit(10)
            ->get([
                'id',
                'name',
                'slug',
            ]);

        $data = Cache::remember(
            'admin_dashboard_analytics',
            now()->addMinutes(5),
            function () use (
                $postsPerMonth,
                $postStatus,
                $productsByCategory
            ) {

                return [
                    'posts_per_month' => $postsPerMonth,
                    'posts_by_status' => $postStatus,
                    'products_by_category' => $productsByCategory,
                ];
            }
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

}