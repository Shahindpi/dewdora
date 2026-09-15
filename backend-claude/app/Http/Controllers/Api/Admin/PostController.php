<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Services\CacheService;





class PostController extends Controller
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()
            ->with([
                'category',
                'user',
            ])
            ->withCount([
                'tags',
                'affiliateProducts',
            ])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Post Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('post_type')) {
            $query->where(
                'post_type',
                $request->string('post_type')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max((int) $request->input('per_page', 15), 1),
            100
        );

        $posts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }


    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Generate Slug
        |--------------------------------------------------------------------------
        */

        $validated['slug'] = $validated['slug']
            ?? Str::slug($validated['title']);

        /*
        |--------------------------------------------------------------------------
        | Author
        |--------------------------------------------------------------------------
        */

        $validated['user_id'] = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Defaults
        |--------------------------------------------------------------------------
        */

        $validated['post_type'] =
            $validated['post_type'] ?? 'article';

        $validated['status'] =
            $validated['status'] ?? 'draft';

        $validated['reading_time'] =
            $validated['reading_time'] ?? 1;

        $validated['allow_comments'] =
            $validated['allow_comments'] ?? false;

        /*
        |--------------------------------------------------------------------------
        | Create Post
        |--------------------------------------------------------------------------
        */

        $post = Post::create($validated);

        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $post->load([
            'category',
            'user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear Cache
        |--------------------------------------------------------------------------
        */

        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully.',
            'data' => $post,
        ], 201);
    }


    /**
     * Synchronize tags for a post.
     */
    public function syncTags(
        Request $request,
        Post $post
    ): JsonResponse {

        $validated = $request->validate([
            'tag_ids' => [
                'required',
                'array',
            ],

            'tag_ids.*' => [
                'integer',
                'exists:tags,id',
            ],
        ]);

        $post->tags()->sync(
            $validated['tag_ids']
        );

        $post->load('tags');

        return response()->json([
            'success' => true,
            'message' => 'Post tags updated successfully.',
            'data' => [
                'post_id' => $post->id,
                'tags' => $post->tags,
            ],
        ]);
    }

    /**
     * Synchronize affiliate products for a post.
     */
    public function syncAffiliateProducts(
        Request $request,
        Post $post
    ): JsonResponse {

        $validated = $request->validate([
            'products' => [
                'required',
                'array',
                'min:1',
            ],

            'products.*.affiliate_product_id' => [
                'required',
                'integer',
                'exists:affiliate_products,id',
            ],

            'products.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'products.*.is_primary' => [
                'nullable',
                'boolean',
            ],
        ]);

        $syncData = [];

        foreach ($validated['products'] as $product) {

            $syncData[
                $product['affiliate_product_id']
            ] = [
                'sort_order' =>
                    $product['sort_order'] ?? 0,

                'is_primary' =>
                    $product['is_primary'] ?? false,
            ];
        }

        $post->affiliateProducts()->sync(
            $syncData
        );

        $post->load([
            'affiliateProducts',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Affiliate products updated successfully.',

            'data' => [
                'post_id' => $post->id,

                'products' =>
                    $post->affiliateProducts,
            ],
        ]);
    }


    /**
     * Display the specified post.
     */
    public function show(Post $post): JsonResponse
    {
        $post->load([
            'category',
            'user',
            'tags',
            'affiliateProducts',
            'seoMeta',
        ]);

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }


    /**
     * Update the specified post.
     */
    public function update(
        UpdatePostRequest $request,
        Post $post
    ): JsonResponse {

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Slug
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['title']) &&
            !isset($validated['slug'])
        ) {
            $validated['slug'] =
                Str::slug($validated['title']);
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $post->update($validated);

        /*
        |--------------------------------------------------------------------------
        | Reload
        |--------------------------------------------------------------------------
        */

        $post->refresh();

        $post->load([
            'category',
            'user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear Cache
        |--------------------------------------------------------------------------
        */

        CacheService::clearPost($post->slug);
        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully.',
            'data' => $post,
        ]);
    }


    public function destroy(Post $post): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Clear Cache Before Delete
        |--------------------------------------------------------------------------
        */

        CacheService::clearPost($post->slug);

        $post->delete();

        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully.',
        ]);
    }
}