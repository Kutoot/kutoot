<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreMerchantRequest;
use App\Http\Requests\Api\V1\Admin\UpdateMerchantRequest;
use App\Http\Resources\MerchantResource;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / Merchants
 */
class MerchantController extends Controller
{
    /**
     * List all merchants.
     *
     * @queryParam search string Search by name.
     * @queryParam filter[is_active] boolean Filter by active status.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Merchant::class);

        $merchants = Merchant::query()
            ->with(['media'])
            ->withCount('locations')
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->has('filter.is_active'), fn ($q) => $q->where('is_active', $request->boolean('filter.is_active')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return MerchantResource::collection($merchants);
    }

    /**
     * Show a merchant.
     */
    public function show(Merchant $merchant): MerchantResource
    {
        $this->authorize('view', $merchant);

        $merchant->load(['locations', 'media']);
        $merchant->loadCount('locations');

        return new MerchantResource($merchant);
    }

    /**
     * Create a new merchant.
     */
    public function store(StoreMerchantRequest $request): JsonResponse
    {
        $merchant = Merchant::create($request->validated());

        $merchant->load('media');

        return (new MerchantResource($merchant))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a merchant.
     */
    public function update(UpdateMerchantRequest $request, Merchant $merchant): MerchantResource
    {
        $merchant->update($request->validated());

        $merchant->load(['locations', 'media']);

        return new MerchantResource($merchant);
    }

    /**
     * Delete a merchant.
     */
    public function destroy(Merchant $merchant): JsonResponse
    {
        $this->authorize('delete', $merchant);

        $merchant->delete();

        return response()->json(['message' => 'Merchant deleted.'], 200);
    }
}
