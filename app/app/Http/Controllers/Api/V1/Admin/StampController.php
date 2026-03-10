<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StampResource;
use App\Models\Campaign;
use App\Models\Stamp;
use App\Models\User;
use App\Services\StampService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / Stamps
 */
class StampController extends Controller
{
    public function __construct(
        protected StampService $stampService,
    ) {}

    /**
     * List all stamps.
     *
     * @queryParam filter[user_id] int Filter by user.
     * @queryParam filter[campaign_id] int Filter by campaign.
     * @queryParam filter[source] string Filter by source.
     * @queryParam search string Search by code.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Stamp::class);

        $stamps = Stamp::query()
            ->with(['user', 'campaign', 'transaction'])
            ->when($request->input('filter.user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->input('filter.campaign_id'), fn ($q, $id) => $q->where('campaign_id', $id))
            ->when($request->input('filter.source'), fn ($q, $s) => $q->where('source', $s))
            ->when($request->input('search'), fn ($q, $s) => $q->where('code', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return StampResource::collection($stamps);
    }

    /**
     * Show a stamp.
     */
    public function show(Stamp $stamp): StampResource
    {
        $this->authorize('view', $stamp);

        $stamp->load(['user', 'campaign', 'transaction']);

        return new StampResource($stamp);
    }

    /**
     * Gift stamps to a user for a specific campaign.
     *
     * @bodyParam user_id int required The user to gift stamps to.
     * @bodyParam campaign_id int required The campaign to associate stamps with.
     * @bodyParam count int required Number of stamps to gift (1-100).
     * @bodyParam note string Optional label for the gift stamps (default: "Gift").
     */
    public function gift(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'count' => 'required|integer|min:1|max:100',
            'note' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $campaign = Campaign::findOrFail($validated['campaign_id']);

        if (! $campaign->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign is not active.',
            ], 422);
        }

        $awarded = $this->stampService->awardGiftStamps(
            $user,
            $campaign,
            $validated['count'],
            $validated['note'] ?? 'Gift',
        );

        return response()->json([
            'success' => true,
            'message' => "{$awarded} gift stamp(s) awarded to user #{$user->id}.",
            'data' => [
                'stamps_awarded' => $awarded,
                'user_id' => $user->id,
                'campaign_id' => $campaign->id,
                'note' => $validated['note'] ?? 'Gift',
            ],
        ]);
    }
}
