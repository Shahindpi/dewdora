<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\PostResource;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Support\ApiResponse;
use App\Http\Resources\Api\Collections\PaginatedApiCollection;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * List public categories.
     */
    public function index(Request $request)
    {
        $perPage = min(
            max($request->integer('per_page', 20), 1),
            50
        );

        $cacheKey = 'public_categories_' . md5(
            json_encode($request->query())
        );

        $categories = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            function () use ($request, $perPage) {

                return Category::query()
                    ->withCount('posts')
                    ->when(
                        $request->filled('search'),
                        function ($query) use ($request) {
                            $query->where(
                                'name',
                                'like',
                                '%' . $request->string('search') . '%'
                            );
                        }
                    )
                    ->orderBy('name')
                    ->paginate($perPage);
            }
        );

        return CategoryResource::collection($categories)
            ->additional([
                'success' => true,
            ]);
    }


    /**
     * Show category with published posts.
     */
    public function show(
        Request $request,
        string $slug
    ) {
        /*
        |--------------------------------------------------------------------------
        | Pagination safety
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max(
                $request->integer('per_page', 10),
                1
            ),
            50
        );


        /*
        |--------------------------------------------------------------------------
        | Find category
        |--------------------------------------------------------------------------
        */

        $category = Cache::remember(
            "public_category_{$slug}",
            now()->addMinutes(30),
            function () use ($slug) {

                return Category::query()
                    ->with([
                        'seoMeta',
                    ])
                    ->where('slug', $slug)
                    ->firstOrFail();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Get published posts
        |--------------------------------------------------------------------------
        */

        $posts = $category
            ->posts()
            ->with([
                'category',
                'user',
                'seoMeta',
            ])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where(
                'published_at',
                '<=',
                now()
            )
            ->latest('published_at')
            ->paginate($perPage);


        /*
        |--------------------------------------------------------------------------
        | Return response
        |--------------------------------------------------------------------------
        */

        return ApiResponse::success(
            [
                'category' => new CategoryResource(
                    $category
                ),

                'posts' => PostResource::collection(
                    $posts
                ),
            ],
            'Category retrieved successfully.'
        );
    }
}