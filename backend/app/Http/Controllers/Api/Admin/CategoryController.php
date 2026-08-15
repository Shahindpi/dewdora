<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:categories,slug',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['slug'] =
            $validated['slug']
            ?? Str::slug($validated['name']);

        $category = Category::create($validated);

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
        Request $request,
        Category $category
    ): JsonResponse {

        $validated = $request->validate([
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
                'unique:categories,slug,' . $category->id,
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        if (
            isset($validated['name']) &&
            !isset($validated['slug'])
        ) {
            $validated['slug'] =
                Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category->fresh(),
        ]);
    }


    /**
     * Delete category.
     */
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

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }
}