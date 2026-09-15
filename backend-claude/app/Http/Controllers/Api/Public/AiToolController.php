<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\AiTool;
use Illuminate\Http\Request;
use App\Support\ApiResponse;

use App\Http\Resources\Api\AiToolResource;
use Illuminate\Support\Facades\Cache;

class AiToolController extends Controller
{
    /**
     * List published/active AI tools.
     */
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 10), 1), 50);

        $cacheKey = 'public_ai_tools_'.md5(json_encode($request->query()));

        $tools = Cache::remember(
            $cacheKey,
            now()->addMinutes(20),
            function () use ($request, $perPage) {

                return AiTool::query()
                    ->with(['category', 'seoMeta'])

                    ->where('status', true)

                    ->when(
                        $request->filled('category'),
                        function ($query) use ($request) {
                            $query->whereHas(
                                'category',
                                function ($categoryQuery) use ($request) {
                                    $categoryQuery->where('slug', $request->string('category'));
                                }
                            );
                        }
                    )

                    ->when(
                        $request->filled('pricing_type'),
                        fn ($query) => $query->where('pricing_type', $request->string('pricing_type'))
                    )

                    ->when(
                        $request->boolean('featured'),
                        fn ($query) => $query->where('featured', true)
                    )

                    ->when(
                        $request->filled('search'),
                        function ($query) use ($request) {
                            $search = $request->string('search');

                            $query->where(function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                    ->orWhere('short_description', 'like', "%{$search}%");
                            });
                        }
                    )

                    ->orderByDesc('featured')
                    ->orderByDesc('rating')

                    ->paginate($perPage);
            }
        );

        return AiToolResource::collection($tools)
            ->additional([
                'success' => true,
            ]);
    }

    /**
     * Show a single AI tool.
     */
    public function show(string $slug)
    {
        $tool = Cache::remember(
            "public_ai_tool_{$slug}",
            now()->addMinutes(20),
            function () use ($slug) {

                return AiTool::query()
                    ->with(['category', 'seoMeta', 'faqs'])
                    ->where('slug', $slug)
                    ->where('status', true)
                    ->firstOrFail();
            }
        );

        return ApiResponse::success(
            ['tool' => new AiToolResource($tool)],
            'AI tool retrieved successfully.'
        );
    }
}
