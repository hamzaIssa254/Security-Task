<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Services\RoleService;

class RoleController extends Controller
{
    protected $roleService;
    /**
     * Summary of __construct
     * @param \App\Services\RoleService $roleService
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;

    }

    /**
     * Summary of index
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $roles = $this->roleService->getAllRoles();
        return response()->json($roles);
    }

    /**
     * Summary of store
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'users' => 'nullable|array',
        ]);

        $role = $this->roleService->createRole($data);
        return response()->json(['message' => 'Role created successfully', 'role' => $role]);
    }

    /**
     * Summary of update
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Role $role
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'users' => 'nullable|array',
        ]);

        $updatedRole = $this->roleService->updateRole($role, $data);
        return response()->json(['message' => 'Role updated successfully', 'role' => $updatedRole]);
    }

    /**
     * Summary of destroy
     * @param \App\Models\Role $role
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role)
    {
        $this->roleService->deleteRole($role);
        return response()->json(['message' => 'Role deleted successfully']);
    }
}
