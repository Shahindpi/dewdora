<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\PostController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\TagController;
use App\Http\Controllers\Api\Admin\AffiliateProductController;
use App\Http\Controllers\Api\Admin\SeoMetaController;
use App\Http\Controllers\Api\Public\PostController as PublicPostController;
use App\Http\Controllers\Api\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\Public\TagController as PublicTagController;
use App\Http\Controllers\Api\Public\AffiliateProductController as PublicAffiliateProductController;
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

        });