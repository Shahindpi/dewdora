<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Services\CacheService;


class CategoryController extends Controller
{
    /**
     * List categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()
            ->withCount('posts')
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->paginate(
            min(
                max((int) $request->input('per_page', 20), 1),
                100
            )
        );

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }


    /**
     * Create category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['slug'] =
            $validated['slug']
            ?? Str::slug($validated['name']);

        $category = Category::create($validated);

        /*
        |--------------------------------------------------------------------------
        | Clear Cache
        |--------------------------------------------------------------------------
        */

        CacheService::clearCategory($category->slug);
        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }


    /**
     * Show category.
     */
    public function show(Category $category): JsonResponse
    {
        $category->loadCount('posts');

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }


    /**
     * Update category.
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Store Previous Slug
        |--------------------------------------------------------------------------
        */

        $oldSlug = $category->slug;

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Generate Slug From Name (If Not Provided)
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['name']) &&
            !isset($validated['slug'])
        ) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Category
        |--------------------------------------------------------------------------
        */

        $category->update($validated);

        /*
        |--------------------------------------------------------------------------
        | Refresh Model
        |--------------------------------------------------------------------------
        */

        $category->refresh();

        /*
        |--------------------------------------------------------------------------
        | Clear Cache
        |--------------------------------------------------------------------------
        */

        // Clear old slug cache (if slug changed)
        CacheService::clearCategory($oldSlug);

        // Clear current slug cache
        CacheService::clearCategory($category->slug);

        // Clear cached category lists and dashboard statistics
        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category,
        ]);
    }


    public function destroy(Category $category): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Protect categories that still contain posts
        |--------------------------------------------------------------------------
        */

        if ($category->posts()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a category containing posts.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Clear Cache Before Delete
        |--------------------------------------------------------------------------
        */

        CacheService::clearCategory($category->slug);

        $category->delete();

        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }
}