<?php

namespace App\Http\Middleware;

use App\Models\MerchantLocation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMerchantLocationAccess
{
    /**
     * Verify the authenticated user has access to the merchant location in the route.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $merchantLocation = $request->route('merchantLocation');

        // Resolve the model if it's an ID
        if (! $merchantLocation instanceof MerchantLocation) {
            $merchantLocation = MerchantLocation::find($merchantLocation);
        }

        if (! $merchantLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant location not found.',
            ], 404);
        }

        $user = $request->user();

        // Check that the user is associated with this merchant location via pivot
        $hasAccess = $user->merchantLocations()
            ->where('merchant_location_id', $merchantLocation->id)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this store location.',
            ], 403);
        }

        // Bind the resolved model to the request for controller use
        $request->route()->setParameter('merchantLocation', $merchantLocation);

        // Also store the pivot role for convenience
        $pivot = $user->merchantLocations()
            ->where('merchant_location_id', $merchantLocation->id)
            ->first()?->pivot;

        $request->attributes->set('merchant_role', $pivot?->role ?? 'staff');

        return $next($request);
    }
}
