<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\PostController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\TagController;
use App\Http\Controllers\Api\Admin\AffiliateProductController;

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

            Route::get('/dashboard', function (Request $request) {

                return response()->json([
                    'success' => true,

                    'message' => 'Welcome to the admin dashboard API.',

                    'user' => $request->user()->only([
                        'id',
                        'name',
                        'email',
                    ]),
                ]);

            });

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


        });

});