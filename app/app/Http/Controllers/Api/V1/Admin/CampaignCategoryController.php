<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCampaignCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCampaignCategoryRequest;
use App\Http\Resources\CampaignCategoryResource;
use App\Models\CampaignCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / Campaign Categories
 */
class CampaignCategoryController extends Controller
{
    /**
     * List all campaign categories.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CampaignCategory::class);

        $categories = CampaignCategory::query()
            ->withCount('campaigns')
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return CampaignCategoryResource::collection($categories);
    }

    /**
     * Show a campaign category.
     */
    public function show(CampaignCategory $campaignCategory): CampaignCategoryResource
    {
        $this->authorize('view', $campaignCategory);

        $campaignCategory->loadCount('campaigns');

        return new CampaignCategoryResource($campaignCategory);
    }

    /**
     * Create a new campaign category.
     */
    public function store(StoreCampaignCategoryRequest $request): JsonResponse
    {
        $category = CampaignCategory::create($request->validated());

        return (new CampaignCategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a campaign category.
     */
    public function update(UpdateCampaignCategoryRequest $request, CampaignCategory $campaignCategory): CampaignCategoryResource
    {
        $campaignCategory->update($request->validated());

        return new CampaignCategoryResource($campaignCategory);
    }

    /**
     * Delete a campaign category.
     */
    public function destroy(CampaignCategory $campaignCategory): JsonResponse
    {
        $this->authorize('delete', $campaignCategory);

        $campaignCategory->delete();

        return response()->json(['message' => 'Campaign category deleted.'], 200);
    }
}
