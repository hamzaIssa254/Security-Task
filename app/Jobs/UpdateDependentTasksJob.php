<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateDependentTasksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle()
    {
        $dependentTasks = $this->task->tasksDependentOnThis;

        foreach ($dependentTasks as $dependentTask) {
            $dependenciesCompleted = $dependentTask->tasksThisDependsOn->every(function ($dependency) {
                return $dependency->status === 'Completed';
            });

            if ($dependenciesCompleted) {
                $dependentTask->update(['status' => 'Open']);
            }
        }
    }
}
