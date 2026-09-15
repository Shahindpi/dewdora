<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\PostController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\TagController;
use App\Http\Controllers\Api\Admin\AffiliateProductController;
use App\Http\Controllers\Api\Admin\SeoMetaController;
use App\Http\Controllers\Api\Admin\AiToolController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\InternalLinkController;
use App\Http\Controllers\Api\Admin\AnalyticsSummaryController;
use App\Http\Controllers\Api\Public\PostController as PublicPostController;
use App\Http\Controllers\Api\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\Public\TagController as PublicTagController;
use App\Http\Controllers\Api\Public\AffiliateProductController as PublicAffiliateProductController;
use App\Http\Controllers\Api\Public\AiToolController as PublicAiToolController;
use App\Http\Controllers\Api\AnalyticsEventController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\SeoController;
use App\Http\Controllers\Api\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

// Authentication
Route::post('/login', [
    AuthController::class,
    'login',
]);


/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Current authenticated user
    Route::get('/me', [
        AuthController::class,
        'me',
    ]);

    // Logout
    Route::post('/logout', [
        AuthController::class,
        'logout',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Admin API Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')
        ->prefix('admin')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Admin Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get('/dashboard', [DashboardController::class, 'index',]);
            Route::get('/dashboard/analytics', [DashboardController::class, 'analytics',]);

            /*
            |--------------------------------------------------------------------------
            | Posts
            |--------------------------------------------------------------------------
            */

            Route::put('/posts/{post}/tags', [PostController::class, 'syncTags']);
            
            Route::put('/posts/{post}/affiliate-products', [PostController::class, 'syncAffiliateProducts']);

            Route::apiResource('posts', PostController::class);

            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */

            Route::apiResource('categories', CategoryController::class);

            /*
            |--------------------------------------------------------------------------
            | Tags
            |--------------------------------------------------------------------------
            */

            Route::apiResource('tags', TagController::class);
            

            /*
            |--------------------------------------------------------------------------
            | Affiliate Products
            |--------------------------------------------------------------------------
            */

            Route::apiResource('affiliate-products', AffiliateProductController::class);

            /*
            |--------------------------------------------------------------------------
            | AI Tools
            |--------------------------------------------------------------------------
            */

            Route::apiResource('ai-tools', AiToolController::class);

            /*
            |--------------------------------------------------------------------------
            | FAQs
            |--------------------------------------------------------------------------
            */

            Route::post('/faqs', [FaqController::class, 'store']);
            Route::put('/faqs/{faq}', [FaqController::class, 'update']);
            Route::delete('/faqs/{faq}', [FaqController::class, 'destroy']);

            /*
            |--------------------------------------------------------------------------
            | Internal Links
            |--------------------------------------------------------------------------
            */

            Route::post('/internal-links', [InternalLinkController::class, 'store']);
            Route::delete('/internal-links/{internalLink}', [InternalLinkController::class, 'destroy']);

            /*
            |--------------------------------------------------------------------------
            | Analytics
            |--------------------------------------------------------------------------
            */

            Route::get('/analytics/summary', [AnalyticsSummaryController::class, 'index']);


            /*
            |--------------------------------------------------------------------------
            | SEO
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/posts/{post}/seo',
                [SeoMetaController::class, 'updatePostSeo']
            );

            Route::delete(
                '/posts/{post}/seo',
                [SeoMetaController::class, 'destroyPostSeo']
            );

            Route::put(
                '/affiliate-products/{affiliateProduct}/seo',
                [
                    SeoMetaController::class,
                    'updateAffiliateProductSeo',
                ]
            );

            Route::delete(
                '/affiliate-products/{affiliateProduct}/seo',
                [
                    SeoMetaController::class,
                    'destroyAffiliateProductSeo',
                ]
            );



        });

        
        
    });
        
        
        /*
        |--------------------------------------------------------------------------
        | Public API
        |--------------------------------------------------------------------------
        */
        Route::middleware('throttle:api')->prefix('public')->group(function () {

            Route::get('/posts', [
                PublicPostController::class,
                'index',
            ]);

            Route::get('/posts/{slug}', [
                PublicPostController::class,
                'show',
            ]);

            Route::get('/categories', [
                PublicCategoryController::class,
                'index',
            ]);

            Route::get('/categories/{slug}', [
                PublicCategoryController::class,
                'show',
            ]);

            Route::get('/tags', [
                PublicTagController::class,
                'index',
            ]);

            Route::get('/tags/{slug}', [
                PublicTagController::class,
                'show',
            ]);

            Route::get('/products', [
                PublicAffiliateProductController::class,
                'index',
            ]);

            Route::get('/products/{slug}', [
                PublicAffiliateProductController::class,
                'show',
            ]);

            /*
            |--------------------------------------------------------------------------
            | AI Tools
            |--------------------------------------------------------------------------
            */

            Route::get('/ai-tools', [
                PublicAiToolController::class,
                'index',
            ]);

            Route::get('/ai-tools/{slug}', [
                PublicAiToolController::class,
                'show',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Internal Links (read - "related posts" widget)
            |--------------------------------------------------------------------------
            | Reuses Api\Admin\InternalLinkController::forPost() - it's a
            | read-only method with no side effects, so it's exposed here
            | without auth rather than duplicating the query in a second
            | controller.
            */

            Route::get('/internal-links/post/{postId}', [
                InternalLinkController::class,
                'forPost',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Newsletter
            |--------------------------------------------------------------------------
            */

            Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
            Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm']);
            Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe']);

            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            Route::post('/contact', [ContactController::class, 'store']);

            /*
            |--------------------------------------------------------------------------
            | Analytics (write-only beacon - no auth, rate-limited like the rest
            | of this group)
            |--------------------------------------------------------------------------
            */

            Route::post('/analytics/events', [AnalyticsEventController::class, 'store']);

            /*
            |--------------------------------------------------------------------------
            | SEO (sitemap data feed + RSS)
            |--------------------------------------------------------------------------
            */

            Route::get('/seo/sitemap-data', [SeoController::class, 'sitemapData']);
            Route::get('/feed.xml', [SeoController::class, 'rss']);

        });