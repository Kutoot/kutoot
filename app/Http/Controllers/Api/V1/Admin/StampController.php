<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StampResource;
use App\Models\Stamp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / Stamps
 */
class StampController extends Controller
{
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
}
