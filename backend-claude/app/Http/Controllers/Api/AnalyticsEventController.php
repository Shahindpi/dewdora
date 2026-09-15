<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\AiTool;
use App\Models\AnalyticsEvent;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Support\ApiResponse;

class AnalyticsEventController extends Controller
{
    private const MODEL_MAP = [
        'post' => Post::class,
        'affiliate_product' => AffiliateProduct::class,
        'ai_tool' => AiTool::class,
    ];

    /**
     * Public write endpoint - called from the frontend (e.g. via
     * navigator.sendBeacon on an affiliate CTA click, or on page unload)
     * to log first-party events alongside GA4.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type' => ['required', 'in:page_view,affiliate_click,ai_tool_click,newsletter_signup,search,outbound_click'],
            'trackable_type' => ['nullable', 'in:post,affiliate_product,ai_tool'],
            'trackable_id' => ['nullable', 'integer'],
            'url' => ['nullable', 'string', 'max:2048'],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'session_id' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        AnalyticsEvent::create([
            'event_type' => $validated['event_type'],
            'trackable_type' => isset($validated['trackable_type'])
                ? self::MODEL_MAP[$validated['trackable_type']]
                : null,
            'trackable_id' => $validated['trackable_id'] ?? null,
            'url' => $validated['url'] ?? null,
            'referrer' => $validated['referrer'] ?? null,
            'utm_source' => $validated['utm_source'] ?? null,
            'utm_medium' => $validated['utm_medium'] ?? null,
            'utm_campaign' => $validated['utm_campaign'] ?? null,
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
            'session_id' => $validated['session_id'] ?? $request->session()->getId(),
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return ApiResponse::success(null, 'Event recorded.', 201);
    }
}
