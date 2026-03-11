<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Newsletter
 */
class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     *
     * Subscribes an email address to the newsletter. If the email was previously
     * unsubscribed, it will be reactivated.
     *
     * @bodyParam email string required A valid email address. Example: user@example.com
     *
     * @response 200 { "message": "Subscribed successfully.", "data": { "email": "user@example.com" } }
     * @response 422 { "message": "The email field is required." }
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $subscriber = NewsletterSubscriber::updateOrCreate(
            ['email' => strtolower(trim($request->input('email')))],
            [
                'is_active' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        return response()->json([
            'message' => 'Subscribed successfully.',
            'data' => [
                'email' => $subscriber->email,
            ],
        ]);
    }
}
