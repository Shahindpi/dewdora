<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Resources\Api\PostResource;
use App\Support\ApiResponse;
use App\Http\Resources\Api\Collections\PaginatedApiCollection;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    /**
     * List published posts.
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

        $cacheKey = 'public_posts_' . md5(
            json_encode($request->query())
        );

        /*
        |--------------------------------------------------------------------------
        | Get Posts (Cached)
        |--------------------------------------------------------------------------
        */

        $posts = Cache::remember(
            $cacheKey,
            now()->addMinutes(10),
            function () use ($request, $perPage) {

                return Post::query()
                    ->with([
                        'category',
                        'tags',
                        'seoMeta',
                    ])

                    // Only published posts
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())

                    // Category filter
                    ->when(
                        $request->filled('category'),
                        function ($query) use ($request) {
                            $query->whereHas('category', function ($categoryQuery) use ($request) {
                                $categoryQuery->where(
                                    'slug',
                                    $request->string('category')
                                );
                            });
                        }
                    )

                    // Tag filter
                    ->when(
                        $request->filled('tag'),
                        function ($query) use ($request) {
                            $query->whereHas('tags', function ($tagQuery) use ($request) {
                                $tagQuery->where(
                                    'slug',
                                    $request->string('tag')
                                );
                            });
                        }
                    )

                    // Search
                    ->when(
                        $request->filled('search'),
                        function ($query) use ($request) {
                            $search = $request->string('search');

                            $query->where(function ($q) use ($search) {
                                $q->where('title', 'like', "%{$search}%")
                                    ->orWhere('excerpt', 'like', "%{$search}%");
                            });
                        }
                    )

                    // Sorting
                    ->when(
                        $request->filled('sort'),
                        function ($query) use ($request) {
                            match ($request->string('sort')->value()) {
                                'oldest' => $query->orderBy('published_at'),
                                'popular' => $query->orderByDesc('views'),
                                default => $query->orderByDesc('published_at'),
                            };
                        },
                        fn ($query) => $query->orderByDesc('published_at')
                    )

                    ->paginate($perPage);
            }
        );

        return PostResource::collection($posts)
            ->additional([
                'success' => true,
            ]);
    }

    /**
     * Show a single published post.
     */
    public function show(string $slug)
    {
        /*
        |--------------------------------------------------------------------------
        | Find the post
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Find the Post (Cached)
        |--------------------------------------------------------------------------
        */

        $post = Cache::remember(
            "public_post_{$slug}",
            now()->addMinutes(15),
            function () use ($slug) {

                return Post::query()
                    ->with([
                        'category',

                        'tags',

                        'affiliateProducts' => function ($query) {
                            $query
                                ->with([
                                    'brand',
                                    'category',
                                    'seoMeta',
                                ])
                                ->where('status', true)
                                ->orderBy('post_product.is_primary', 'desc')
                                ->orderBy('post_product.sort_order');
                        },

                        'seoMeta',
                    ])
                    ->where('slug', $slug)
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->firstOrFail();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Get related posts
        |--------------------------------------------------------------------------
        */

        $tagIds = $post->tags
            ->pluck('id')
            ->values()
            ->all();

        $relatedPosts = Post::query()
            ->with([
                'category',
                'tags',
            ])
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($query) use ($post, $tagIds) {

                /*
                |--------------------------------------------------------------------------
                | Same category
                |--------------------------------------------------------------------------
                */

                $query->where(
                    'category_id',
                    $post->category_id
                );


                /*
                |--------------------------------------------------------------------------
                | OR shared tags
                |--------------------------------------------------------------------------
                */

                if (!empty($tagIds)) {
                    $query->orWhereHas(
                        'tags',
                        function ($tagQuery) use ($tagIds) {
                            $tagQuery->whereIn(
                                'tags.id',
                                $tagIds
                            );
                        }
                    );
                }
            })
            ->latest('published_at')
            ->limit(6)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Return response
        |--------------------------------------------------------------------------
        */

        return ApiResponse::success(
            [
                'post' => new PostResource($post),

                'related_posts' =>
                    PostResource::collection($relatedPosts),
            ],
            'Post retrieved successfully.'
        );
    }
}