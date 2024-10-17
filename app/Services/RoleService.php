<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RoleService
{
    /**
     * Summary of getAllRoles
     * @throws \Exception
     * @return mixed
     */
    public function getAllRoles()
    {
        try {

            return Cache::remember('roles_with_users', now()->addMinutes(10), function () {
                return Role::with('users')->get();
            });
        } catch (\Exception $e) {
            Log::error('Error fetching roles: ' . $e->getMessage());
            throw new \Exception('Error fetching roles');
        }
    }

    /**
     * Summary of createRole
     * @param array $data
     * @throws \Exception
     * @return Role|\Illuminate\Database\Eloquent\Model
     */
    public function createRole(array $data)
    {
        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $data['name'],
            ]);


            if (!empty($data['users'])) {
                $role->users()->sync($data['users']);
            }

            DB::commit();


            Cache::forget('roles_with_users');

            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating role: ' . $e->getMessage());
            throw new \Exception('Error creating role');
        }
    }

    /**
     * Summary of updateRole
     * @param \App\Models\Role $role
     * @param array $data
     * @throws \Exception
     * @return Role
     */
    public function updateRole(Role $role, array $data)
    {
        try {
            DB::beginTransaction();

            $role->update([
                'name' => $data['name'],
            ]);


            if (!empty($data['users'])) {
                $role->users()->sync($data['users']);
            }

            DB::commit();


            Cache::forget('roles_with_users');

            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating role: ' . $e->getMessage());
            throw new \Exception('Error updating role');
        }
    }

    /**
     * Summary of deleteRole
     * @param \App\Models\Role $role
     * @throws \Exception
     * @return bool
     */
    public function deleteRole(Role $role)
    {
        try {
            DB::beginTransaction();

            $role->users()->detach();
            $role->delete();

            DB::commit();


            Cache::forget('roles_with_users');

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting role: ' . $e->getMessage());
            throw new \Exception('Error deleting role');
        }
    }
}
