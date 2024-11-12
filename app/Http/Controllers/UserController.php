<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;
    /**
     * Summary of __construct
     * @param \App\Services\UserService $userService
     */
    public function __construct(UserService $userService)
    {


        $this->userService = $userService;
    }
    /**
     * Summary of index
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $users = $this->userService->getAllUsers();
        return ApiResponseService::paginated($users,'retrive success');
    }
    /**
     * Summary of store
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $user = $this->userService->createUser($data, $data['roles']);

        return response()->json(['message' => 'User created successfully', 'user' => $user],201);
    }
    /**
     * Summary of update
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $updatedUser = $this->userService->updateUser($user, $data, $request->roles);

        return response()->json(['message' => 'User updated successfully', 'user' => $updatedUser]);
    }
    /**
     * Summary of destroy
     * @param \App\Models\User $user
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return response()->json(['message' => 'User deleted successfully']);
    }
}
