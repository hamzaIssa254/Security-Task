<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use App\Models\TaskDependency;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckTaskDependencies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle()
    {
      
        $dependentTasks = $this->task->dependentTasks()->with('task')->get();

        foreach ($dependentTasks as $dependency) {
            $dependentTask = $dependency->task;


            $uncompletedDependencies = TaskDependency::where('task_id', $dependentTask->id)
                ->whereHas('dependsOnTask', function ($query) {
                    $query->where('status', '!=', 'Completed');
                })->count();

            if ($uncompletedDependencies == 0) {
                $dependentTask->update(['status' => 'Open']);
            }
        }
    }
}
