<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use App\Support\ApiResponse;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $validated['email']],
            ['status' => 'pending']
        );

        if ($subscriber->status === 'unsubscribed') {
            $subscriber->update(['status' => 'pending', 'unsubscribed_at' => null]);
        }

        // TODO: dispatch a confirmation-email Mailable/Notification here
        // with a link to /api/newsletter/confirm/{confirmation_token}.

        return ApiResponse::success(
            null,
            'Check your inbox to confirm your subscription.',
            201
        );
    }

    public function confirm(string $token)
    {
        $subscriber = NewsletterSubscriber::query()
            ->where('confirmation_token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $subscriber->confirm();

        return ApiResponse::success(null, 'Subscription confirmed.');
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate(['email' => ['required', 'email']]);

        $subscriber = NewsletterSubscriber::query()
            ->where('email', $validated['email'])
            ->firstOrFail();

        $subscriber->unsubscribe();

        return ApiResponse::success(null, 'Unsubscribed.');
    }
}
