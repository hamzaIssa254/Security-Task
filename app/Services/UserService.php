<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{

    /**
     * Summary of getAllUsers
     * @param mixed $perPage
     * @throws \Exception
     * @return mixed
     */
    public function getAllUsers($perPage = 10)
    {
        $cacheKey = 'users_' . $perPage . '_page_' . request('page', 1);

        try {
            return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($perPage) {
                return User::with('roles')->paginate($perPage);
            });
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            throw new \Exception('There was an error fetching users.');
        }
    }

    /**
     * Create a new user and assign roles
     */
    public function createUser(array $data, array $roles)
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            // Assign roles
            $user->roles()->sync($roles);

            DB::commit();

            // Clear cache after adding a user
            Cache::forget('users_*');

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());
            throw new \Exception('There was an error creating the user.');
        }
    }

    /**
     * Update an existing user and re-assign roles
     */
    public function updateUser(User $user, array $data, array $roles)
    {
        try {
            DB::beginTransaction();

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
            ]);

            // Re-assign roles
            $user->roles()->sync($roles);

            DB::commit();

            // Clear cache after updating the user
            Cache::forget('users_*');

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user: ' . $e->getMessage());
            throw new \Exception('There was an error updating the user.');
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user)
    {
        try {
            DB::beginTransaction();

            // Detach all roles before deleting
            $user->roles()->detach();
            $user->delete();

            DB::commit();

            // Clear cache after deleting the user
            Cache::forget('users_*');

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user: ' . $e->getMessage());
            throw new \Exception('There was an error deleting the user.');
        }
    }

    /**
     * Summary of retrieveUsers
     * @param int $id
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function retrieveUsers(int $id)
    {
        if (Auth::user()->role !== 'Admin') {
            throw new AuthorizationException("Access denied. Only admin can delete users.");
        }
        try{
            $deletedUser = User::onlyTrashed()->find($id);
            $deletedUser->restore();

        }catch (AuthorizationException $e) {
            // Handle unauthorized access
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 403);

        } catch (ModelNotFoundException $e) {
            // Handle case where user is not found
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 404);

        }catch (Exception $e) {
            
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
