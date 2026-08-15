<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:120',
                'unique:tags,slug',
            ],
        ]);

        $validated['slug'] =
            $validated['slug']
            ?? Str::slug($validated['name']);

        $tag = Tag::create($validated);

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
        Request $request,
        Tag $tag
    ): JsonResponse {

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:120',
                'unique:tags,slug,' . $tag->id,
            ],
        ]);

        if (
            isset($validated['name']) &&
            !isset($validated['slug'])
        ) {
            $validated['slug'] =
                Str::slug($validated['name']);
        }

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully.',
            'data' => $tag->fresh(),
        ]);
    }


    /**
     * Delete tag.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully.',
        ]);
    }
}