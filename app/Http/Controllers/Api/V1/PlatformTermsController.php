<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PlatformTerms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Platform Terms
 */
class PlatformTermsController extends Controller
{
    /**
     * Current Terms
     *
     * Returns the currently active platform terms and conditions.
     *
     * @unauthenticated
     */
    public function current(): JsonResponse
    {
        $terms = PlatformTerms::active();

        if (! $terms) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id' => $terms->id,
                'version' => $terms->version,
                'title' => $terms->title,
                'content' => $terms->content,
                'published_at' => $terms->published_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Accept Terms
     *
     * Record that the authenticated user has accepted the current platform terms.
     */
    public function accept(Request $request): JsonResponse
    {
        $user = $request->user();
        $terms = PlatformTerms::active();

        if (! $terms) {
            return response()->json(['message' => 'No active terms to accept.'], 404);
        }

        $user->update([
            'terms_accepted_at' => now(),
            'terms_version_id' => $terms->id,
        ]);

        return response()->json([
            'message' => 'Terms accepted successfully.',
            'accepted_version' => $terms->version,
        ]);
    }
}
