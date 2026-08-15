<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

        });

});