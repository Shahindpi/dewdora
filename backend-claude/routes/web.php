<?php

use App\Http\Controllers\GoLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Cloaked affiliate/AI-tool outbound link, e.g. https://yoursite.com/go/some-tool-a1b2
// Kept on the `web` middleware group (not routes/api.php) since it issues
// a real 302 redirect rather than a JSON response.
Route::get('/go/{slug}', GoLinkController::class)->name('go-link');
