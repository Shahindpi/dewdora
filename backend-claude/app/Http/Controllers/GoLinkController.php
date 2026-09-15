<?php

namespace App\Http\Controllers;

use App\Models\AffiliateProduct;
use App\Models\AiTool;
use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

/**
 * Handles outbound affiliate redirects: GET /go/{slug}
 *
 * The ONLY place the real affiliate_url is ever emitted to a client - the
 * public API never returns it (see AffiliateProductResource/AiToolResource,
 * both of which only expose `redirect_url` pointing here). Registered on
 * the `web` middleware group in routes/web.php since it issues a browser
 * redirect rather than JSON.
 */
class GoLinkController extends Controller
{
    public function __invoke(Request $request, string $slug): RedirectResponse
    {
        $product = AffiliateProduct::query()
            ->where('cloaked_slug', $slug)
            ->where('status', true)
            ->first();

        $tool = $product ? null : AiTool::query()
            ->where('cloaked_slug', $slug)
            ->where('status', true)
            ->first();

        $target = $product ?? $tool;

        abort_if(! $target, 404);

        $target->registerClick();

        AnalyticsEvent::create([
            'event_type' => $product ? 'affiliate_click' : 'ai_tool_click',
            'trackable_type' => $target::class,
            'trackable_id' => $target->id,
            'url' => $request->fullUrl(),
            'referrer' => $request->header('referer'),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
            'session_id' => $request->session()->getId(),
        ]);

        $destination = $product ? $product->affiliate_url : $tool->affiliate_url;
        $destination = $destination ?: $target->website_url;

        return redirect()->away($destination, 302);
    }
}
