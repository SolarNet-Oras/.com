<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'is_active' => $role->is_active,
                    'permissions_count' => $role->permissions->count(),
                    'users_count' => $role->users->count(),
                    'created_at' => $role->created_at,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        // Assign permissions if provided
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Role created successfully',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ], 201);
    }

    /**
     * Display the specified role
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::with('permissions', 'users')->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'is_active' => $role->is_active,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
                'permissions' => $role->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'group' => $permission->group,
                    ];
                }),
                'users_count' => $role->users->count(),
            ],
        ]);
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role->update($request->only(['name', 'display_name', 'description', 'is_active']));

        return response()->json([
            'status' => 'success',
            'message' => 'Role updated successfully',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'is_active' => $role->is_active,
            ],
        ]);
    }

    /**
     * Remove the specified role
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        
        // Prevent deleting super_admin role
        if ($role->name === 'super_admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete super administrator role',
            ], 403);
        }
        
        $role->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Sync permissions for a role
     */
    public function syncPermissions(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::findOrFail($id);
        $role->syncPermissions($request->permissions);

        return response()->json([
            'status' => 'success',
            'message' => 'Permissions synced successfully',
            'data' => [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]);
    }

    /**
     * Get all available permissions
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::all()->groupBy('group');
        
        return response()->json([
            'status' => 'success',
            'data' => $permissions->map(function ($groupPermissions, $group) {
                return [
                    'group' => $group,
                    'permissions' => $groupPermissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'display_name' => $permission->display_name,
                            'description' => $permission->description,
                        ];
                    }),
                ];
            })->values(),
        ]);
    }
}
