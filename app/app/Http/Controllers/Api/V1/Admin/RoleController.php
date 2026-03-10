<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreRoleRequest;
use App\Http\Requests\Api\V1\Admin\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

/**
 * @tags Admin / Roles
 */
class RoleController extends Controller
{
    /**
     * List all roles.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('manage-permissions');

        $roles = Role::query()
            ->with('permissions')
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return RoleResource::collection($roles);
    }

    /**
     * Show a role.
     */
    public function show(Role $role): RoleResource
    {
        $this->authorize('manage-permissions');

        $role->load('permissions');

        return new RoleResource($role);
    }

    /**
     * Create a new role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions(
                \Spatie\Permission\Models\Permission::whereIn('id', $data['permissions'])->pluck('name')
            );
        }

        $role->load('permissions');

        return (new RoleResource($role))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a role.
     */
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $data = $request->validated();

        $role->update([
            'name' => $data['name'] ?? $role->name,
            'guard_name' => $data['guard_name'] ?? $role->guard_name,
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions(
                \Spatie\Permission\Models\Permission::whereIn('id', $data['permissions'])->pluck('name')
            );
        }

        $role->load('permissions');

        return new RoleResource($role);
    }

    /**
     * Delete a role.
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('manage-permissions');

        $role->delete();

        return response()->json(['message' => 'Role deleted.'], 200);
    }
}
