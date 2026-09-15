<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\AiTool;
use App\Models\Faq;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Support\ApiResponse;
use App\Http\Resources\Api\FaqResource;
use App\Services\CacheService;

class FaqController extends Controller
{
    private const MODEL_MAP = [
        'post' => Post::class,
        'affiliate_product' => AffiliateProduct::class,
        'ai_tool' => AiTool::class,
    ];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'faqable_type' => ['required', Rule::in(array_keys(self::MODEL_MAP))],
            'faqable_id' => ['required', 'integer'],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $modelClass = self::MODEL_MAP[$validated['faqable_type']];
        $subject = $modelClass::findOrFail($validated['faqable_id']);

        $faq = $subject->faqs()->create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        CacheService::clearPublicCaches();

        return ApiResponse::success(
            new FaqResource($faq),
            'FAQ created successfully.',
            201
        );
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]));

        CacheService::clearPublicCaches();

        return ApiResponse::success(new FaqResource($faq), 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        CacheService::clearPublicCaches();

        return ApiResponse::success(null, 'FAQ deleted successfully.');
    }
}
