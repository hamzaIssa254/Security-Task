<?php


namespace App\Services;

use App\Models\Task;
use App\Models\TaskReport;
use App\Models\TaskDependency;
use Illuminate\Support\Facades\Log;

class ReportService
{
    /**
     * Summary of generateDailyReport
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generateDailyReport()
    {

        $tasks = Task::whereDate('completed_at', today())->get();

        if ($tasks->isEmpty()) {
            return response()->json(['message' => 'No completed tasks for today.'], 200);
        }


        $reportData = [];

        foreach ($tasks as $task) {

            $dependentTasks = TaskDependency::where('depends_on_task_id', $task->id)
                                ->with('task')
                                ->get();


            $taskDependencies = TaskDependency::where('task_id', $task->id)
                                ->with('dependsOnTask')
                                ->get();


            $reportData[] = [
                'task' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'completed_at' => $task->completed_at,
                'tasks_depending_on_this' => $dependentTasks->pluck('task.title')->toArray(),
                'tasks_this_depends_on' => $taskDependencies->pluck('dependsOnTask.title')->toArray(),
            ];
        }


        $report = TaskReport::create([
            'report' => json_encode($reportData)
        ]);


        $taskIds = $tasks->pluck('id')->toArray();
        $report->tasks()->attach($taskIds);

        return response()->json(['message' => 'Daily task report generated successfully.'], 200);
    }

}
