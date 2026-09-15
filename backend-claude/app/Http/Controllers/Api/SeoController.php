<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\AiTool;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Response;
use App\Support\ApiResponse;

class SeoController extends Controller
{
    /**
     * Lightweight {url, lastmod} list for every indexable page, consumed by
     * the frontend's app/sitemap.ts (Next.js generates the actual XML so it
     * can also list its own static routes like /about, /contact).
     */
    public function sitemapData()
    {
        $posts = Post::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get(['id', 'slug', 'post_type', 'updated_at'])
            ->map(fn (Post $post) => [
                'url' => $this->postUrlPath($post),
                'lastmod' => $post->updated_at->toAtomString(),
            ]);

        $categories = Category::query()
            ->where('status', true)
            ->get(['slug', 'updated_at'])
            ->map(fn ($c) => [
                'url' => "/categories/{$c->slug}",
                'lastmod' => $c->updated_at->toAtomString(),
            ]);

        $products = AffiliateProduct::query()
            ->where('status', true)
            ->get(['slug', 'updated_at'])
            ->map(fn ($p) => [
                'url' => "/products/{$p->slug}",
                'lastmod' => $p->updated_at->toAtomString(),
            ]);

        $tools = AiTool::query()
            ->where('status', true)
            ->get(['slug', 'updated_at'])
            ->map(fn ($t) => [
                'url' => "/ai-tools/{$t->slug}",
                'lastmod' => $t->updated_at->toAtomString(),
            ]);

        return ApiResponse::success([
            'posts' => $posts,
            'categories' => $categories,
            'products' => $products,
            'ai_tools' => $tools,
        ]);
    }

    /**
     * Full RSS 2.0 feed, served straight from Laravel at /api/feed.xml.
     * Next.js can proxy /rss.xml -> this endpoint (see the frontend's
     * app/rss.xml/route.ts), or roll its own from the same post data.
     */
    public function rss()
    {
        $posts = Post::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('author')
            ->latest('published_at')
            ->limit(50)
            ->get();

        $siteUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');
        $appName = config('app.name');

        $items = $posts->map(function (Post $post) use ($siteUrl) {
            $link = $siteUrl.$this->postUrlPath($post);
            $pubDate = $post->published_at->toRfc2822String();
            $title = e($post->title);
            $description = e($post->excerpt ?? '');

            return <<<XML
            <item>
                <title>{$title}</title>
                <link>{$link}</link>
                <guid isPermaLink="true">{$link}</guid>
                <pubDate>{$pubDate}</pubDate>
                <description>{$description}</description>
            </item>
            XML;
        })->implode("\n");

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0">
        <channel>
            <title>{$appName}</title>
            <link>{$siteUrl}</link>
            <description>Latest posts, reviews, and AI tool coverage.</description>
            <language>en-us</language>
            {$items}
        </channel>
        </rss>
        XML;

        return Response::make($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    private function postUrlPath(Post $post): string
    {
        $type = $post->post_type instanceof \App\Enums\PostType ? $post->post_type->value : $post->post_type;

        $prefix = match ($type) {
            'review' => 'reviews',
            'comparison' => 'comparisons',
            'tutorial' => 'tutorials',
            'deal' => 'deals',
            default => 'blog',
        };

        return "/{$prefix}/{$post->slug}";
    }
}
