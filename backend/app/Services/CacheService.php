<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Clear all public API caches.
     */
    public static function clearPublicCaches(): void
    {
        // Clear cached public list endpoints
        Cache::flush();
    }

    /**
     * Clear admin dashboard caches.
     */
    public static function clearDashboardCaches(): void
    {
        Cache::forget('admin_dashboard_overview');
        Cache::forget('admin_dashboard_analytics');
    }

    /**
     * Clear a single cached post page.
     */
    public static function clearPost(string $slug): void
    {
        Cache::forget("public_post_{$slug}");
    }

    /**
     * Clear a single cached category page.
     */
    public static function clearCategory(string $slug): void
    {
        Cache::forget("public_category_{$slug}");
    }

    /**
     * Clear a single cached tag page.
     */
    public static function clearTag(string $slug): void
    {
        Cache::forget("public_tag_{$slug}");
    }

    /**
     * Clear a single cached affiliate product page.
     */
    public static function clearProduct(string $slug): void
    {
        Cache::forget("public_product_{$slug}");
    }

    /**
     * Clear every cache used by the application.
     */
    public static function clearAll(): void
    {
        self::clearPublicCaches();
        self::clearDashboardCaches();
    }
}