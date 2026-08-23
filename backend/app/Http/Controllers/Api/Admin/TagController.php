<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;



use App\Http\Requests\UpdateTagRequest;
use App\Http\Requests\StoreTagRequest;
use App\Services\CacheService;



class TagController extends Controller
{
    /**
     * List tags.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::query()
            ->withCount('posts')
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where('name', 'like', "%{$search}%");
        }

        $tags = $query->paginate(
            min(
                max((int) $request->input('per_page', 20), 1),
                100
            )
        );

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }


    /**
     * Create tag.
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
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
        | Create Tag
        |--------------------------------------------------------------------------
        */

        $tag = Tag::create($validated);

        /*
        |--------------------------------------------------------------------------
        | Clear Cache
        |--------------------------------------------------------------------------
        */

        CacheService::clearTag($tag->slug);
        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully.',
            'data' => $tag,
        ], 201);
    }

    /**
     * Show tag.
     */
    public function show(Tag $tag): JsonResponse
    {
        $tag->loadCount('posts');

        return response()->json([
            'success' => true,
            'data' => $tag,
        ]);
    }


    /**
     * Update tag.
     */
    public function update(
        UpdateTagRequest $request,
        Tag $tag
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Store Previous Slug
        |--------------------------------------------------------------------------
        */

        $oldSlug = $tag->slug;

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
        | Update Tag
        |--------------------------------------------------------------------------
        */

        $tag->update($validated);

        /*
        |--------------------------------------------------------------------------
        | Refresh Model
        |--------------------------------------------------------------------------
        */

        $tag->refresh();

        /*
        |--------------------------------------------------------------------------
        | Clear Cache
        |--------------------------------------------------------------------------
        */

        CacheService::clearTag($oldSlug);
        CacheService::clearTag($tag->slug);

        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully.',
            'data' => $tag,
        ]);
    }


    /**
     * Delete tag.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Clear Cache Before Delete
        |--------------------------------------------------------------------------
        */

        CacheService::clearTag($tag->slug);

        $tag->delete();

        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully.',
        ]);
    }
}