<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCampaignRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

/**
 * @tags Admin / Campaigns
 */
class CampaignController extends Controller
{
    /**
     * List all campaigns.
     *
     * @queryParam filter[status] string Filter by status (active, paused, completed).
     * @queryParam filter[category_id] int Filter by category.
     * @queryParam search string Search by reward name.
     * @queryParam per_page int Items per page (default 15).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Campaign::class);

        $campaigns = Campaign::query()
            ->with(['category', 'creator', 'plans', 'media'])
            ->withCount(['stamps'])
            ->when($request->input('filter.status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('filter.category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->input('search'), fn ($q, $search) => $q->where('reward_name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return CampaignResource::collection($campaigns);
    }

    /**
     * Show a campaign.
     */
    public function show(Campaign $campaign): CampaignResource
    {
        $this->authorize('view', $campaign);

        $campaign->load(['category', 'creator', 'plans', 'stamps', 'media']);
        $campaign->loadCount(['stamps']);

        return new CampaignResource($campaign);
    }

    /**
     * Create a new campaign.
     */
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['code'])) {
            $data['code'] = strtoupper(Str::random(6));
        } else {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $campaign = Campaign::create($data);

        if (isset($data['plans'])) {
            $campaign->plans()->sync($data['plans']);
        }

        $campaign->load(['category', 'creator', 'plans', 'media']);

        return (new CampaignResource($campaign))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a campaign.
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign): CampaignResource
    {
        $data = $request->validated();

        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $campaign->update($data);

        if (isset($data['plans'])) {
            $campaign->plans()->sync($data['plans']);
        }

        $campaign->load(['category', 'creator', 'plans', 'media']);

        return new CampaignResource($campaign);
    }

    /**
     * Delete a campaign.
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        $this->authorize('delete', $campaign);

        $campaign->delete();

        return response()->json(['message' => 'Campaign deleted.'], 200);
    }
}
