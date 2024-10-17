<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function createTask(User $user)
    {
        return $user->hasRole('admin');
    }

    public function updateTask(User $user, Task $task)
    {
        return $user->hasRole('admin');
    }

    // المستخدم المسند له المهمة فقط يمكنه تغيير حالتها
    public function changeStatus(User $user, Task $task)
    {
        return $user->id === $task->assigned_to;
    }

    // أي مستخدم يمكنه التعليق على المهمة حتى لو لم تكن مسندة إليه
    public function commentOnTask(User $user, Task $task)
    {
        return true;
    }
}
