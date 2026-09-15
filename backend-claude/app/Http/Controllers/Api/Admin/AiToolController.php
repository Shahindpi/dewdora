<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiTool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Http\Requests\StoreAiToolRequest;
use App\Http\Requests\UpdateAiToolRequest;
use App\Services\CacheService;

class AiToolController extends Controller
{
    /**
     * List AI tools.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AiTool::query()
            ->with(['category'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        if ($request->filled('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        $tools = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tools,
        ]);
    }

    /**
     * Create AI tool.
     */
    public function store(StoreAiToolRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['currency'] = $validated['currency'] ?? 'USD';
        $validated['featured'] = $validated['featured'] ?? false;
        $validated['status'] = $validated['status'] ?? true;

        $tool = AiTool::create($validated);

        $tool->load(['category']);

        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'AI tool created successfully.',
            'data' => $tool,
        ], 201);
    }

    /**
     * Show AI tool.
     */
    public function show(AiTool $aiTool): JsonResponse
    {
        $aiTool->load(['category', 'post', 'seoMeta', 'faqs']);

        return response()->json([
            'success' => true,
            'data' => $aiTool,
        ]);
    }

    /**
     * Update AI tool.
     */
    public function update(UpdateAiToolRequest $request, AiTool $aiTool): JsonResponse
    {
        $oldSlug = $aiTool->slug;

        $validated = $request->validated();

        if (isset($validated['name']) && ! isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $aiTool->update($validated);
        $aiTool->refresh();
        $aiTool->load(['category']);

        CacheService::clearAiTool($oldSlug);
        CacheService::clearAiTool($aiTool->slug);
        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'AI tool updated successfully.',
            'data' => $aiTool,
        ]);
    }

    /**
     * Delete AI tool.
     */
    public function destroy(AiTool $aiTool): JsonResponse
    {
        CacheService::clearAiTool($aiTool->slug);

        $aiTool->delete();

        CacheService::clearPublicCaches();
        CacheService::clearDashboardCaches();

        return response()->json([
            'success' => true,
            'message' => 'AI tool deleted successfully.',
        ]);
    }
}
