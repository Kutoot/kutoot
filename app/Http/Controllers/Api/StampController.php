<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStampCodeRequest;
use App\Models\Stamp;
use App\Services\StampService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class StampController extends Controller
{
    public function __construct(protected StampService $stampService) {}

    /**
     * Update a stamp's code with user-selected slot values.
     */
    public function updateCode(UpdateStampCodeRequest $request, Stamp $stamp): JsonResponse
    {
        try {
            $stamp = $this->stampService->updateStampCode(
                $stamp,
                $request->validated('slot_values'),
            );

            return response()->json([
                'message' => 'Stamp code updated successfully.',
                'stamp' => [
                    'id' => $stamp->id,
                    'code' => $stamp->code,
                    'editable_until' => $stamp->editable_until?->toISOString(),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
