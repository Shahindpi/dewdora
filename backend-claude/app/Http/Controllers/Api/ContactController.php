<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Support\ApiResponse;

class ContactController extends Controller
{
    /**
     * No dedicated table on purpose - low-volume marketing-site contact
     * form, not CMS content. Swap Log::info() for a real Mailable once
     * mail is configured.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            // Honeypot field - real users never fill this in.
            'company' => ['prohibited'],
        ]);

        Log::info('Contact form submission', $validated);

        return ApiResponse::success(null, "Thanks — we'll get back to you soon.", 201);
    }
}
