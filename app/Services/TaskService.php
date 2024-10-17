<?php

namespace App\Services;

use Exception;
use App\Models\Task;
use App\Models\Attachment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TaskDependency;
use App\Models\TaskStatusUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\UpdateDependentTasksJob;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class TaskService
{

    /**
     * Summary of createTask
     * @param array $data
     * @throws \Exception
     * @return void
     */
    public function createTask(array $data)
{
    try {
        DB::beginTransaction();

        // إنشاء المهمة
        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'due_date' => $data['due_date'],
            'assigned_to' => $data['assigned_to'],
        ]);


        if (isset($data['dependencies'])) {
            foreach ($data['dependencies'] as $dependsOnTaskId) {
                TaskDependency::create([
                    'task_id' => $task->id,
                    'depends_on_task_id' => $dependsOnTaskId,
                ]);
            }


            $task->update(['status' => 'Blocked']);

            Cache::forget('tasks_');
        }

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('something went wrong: ' . $e->getMessage());
        throw new \Exception('Error while creating this task');
    }
}
    /**
     * Summary of updateTask
     * @param \App\Models\Task $task
     * @param array $data
     * @throws \Exception
     * @return void
     */
    public function updateTask(Task $task, array $data)
    {
        try{
            DB::beginTransaction();
            $task->update(array_filter($data));
             // إذا تم إكمال المهمة، تحقق من المهام التي تعتمد عليها وقم بتحديث حالاتها
             if ($task->status === 'Completed') {
                UpdateDependentTasksJob::dispatch($task);
            }
            DB::commit();

        }catch (\Exception $e){
            DB::rollBack();
            Log::error('something wrong while updating '.$e->getMessage());
            throw new \Exception('wrong while updating this task');
        }
    }
    /**
     * Summary of showTask
     * @param \App\Models\Task $task
     * @return string|Task
     */
    public function showTask(Task $task)
    {
        try{
            return $task->load(['comments','statusUpdates','dependentTasks','dependencies']);

        }catch (\Exception $e){
            return $e->getMessage();
        }
    }
    /**
     * Summary of deleteTask
     * @param \App\Models\Task $task
     * @throws \Exception
     * @return void
     */
    public function deleteTask(Task $task)
    {
        try{
            DB::beginTransaction();
            $task->delete();
            DB::commit();

        }catch (\Exception $e){
            DB::rollBack();
            Log::error('wrong while delete '.$e->getMessage());
            throw new \Exception('wrong while deleting this task');
        }
    }
    /**
     * Summary of listAllTask
     * @param array $filters
     * @param int $perPage
     * @throws \Exception
     * @return mixed
     */
    public function listAllTask(array $filters, int $perPage)
    {




        try {
            // Generate a unique cache key based on filters and pagination
        $cacheKey = 'tasks_' . md5(json_encode($filters) . $perPage . request('page', 1));

        // Check if the cached result exists
        $tasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($filters, $perPage) {

            $priority = $filters['priority'] ?? null; // Filter by priority
            $status = $filters['status'] ?? null;     // Filter by status
            $type = $filters['type'] ?? null;         // Filter by type
            $assigned_to = $filters['assigned_to'] ?? null; // Filter by assigned_to
            $due_date = $filters['due_date'] ?? null; // Filter by due_date

            // Use scope Priority and Status from model Task to filter
            return Task::with(['comments', 'attachments', 'dependencies'])
            ->when($priority, function ($query, $priority) {
                    return $query->where('priority', $priority);
                })
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($type, function ($query, $type) {
                    return $query->where('type', $type);
                })
                ->when($assigned_to, function ($query, $assigned_to) {
                    return $query->where('assigned_to', $assigned_to);
                })
                ->when($due_date, function ($query, $due_date) {
                    return $query->whereDate('due_date', $due_date);
                })
                ->paginate($perPage);
        });

        return $tasks;
        } catch (\Exception $e) {
            Log::error('error listing tasks ' . $e->getMessage());
            throw new \Exception('there is something wrong');
        }
    }

    public function assigneTo(int $id,array $data)
    {
        try{
            $task = Task::findOrFail($id);
            $task->update([
                'assigned_to' => $data['assigned_to']
            ]);

        }catch (\Exception $e) {
            Log::error('error assigning tasks ' . $e->getMessage());
            throw new \Exception('there is something wrong');
        }
    }
    /**
     * Summary of addAttachment
     * @param \Illuminate\Http\Request $request
     * @throws \Exception
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @return Attachment|\Illuminate\Database\Eloquent\Model
     */
    public function addAttachment(Request $request)
    {
        // تأكد من وجود الملف في الطلب
        if (!$request->hasFile('file')) {
            throw new Exception('No file was uploaded.', 400);
        }

        // احصل على الملف
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        // Ensure the file extension is valid and there is no path traversal in the file name
        if (preg_match('/\.[^.]+\./', $originalName)) {
            throw new Exception(trans('general.notAllowedAction'), 403);
        }

        // Check for path traversal attack
        if (strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
            throw new Exception(trans('general.pathTraversalDetected'), 403);
        }

        // Validate the MIME type to ensure it's one of the allowed types
        $allowedMimeTypes = [
            'application/pdf', 'text/plain',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword', 'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg', 'image/png', 'image/gif', 'image/webp'
        ];

        $mime_type = $file->getClientMimeType();

        if (!in_array($mime_type, $allowedMimeTypes)) {
            throw new FileException(trans('general.invalidFileType'), 403);
        }

        // رفع الملف إلى VirusTotal لفحصه
        try {
            $response = Http::withHeaders([
                'x-apikey' => env('VIRUSTOTAL_API_KEY')
            ])->attach(
                'file', file_get_contents($file->getRealPath()), $originalName
            )->post('https://www.virustotal.com/api/v3/files');

            // تحقق من استجابة VirusTotal
            if ($response->failed()) {
                throw new Exception(trans('general.virusScanFailed'), 500);
            }

            $scanResult = $response->json();

            // إذا تم العثور على فيروس
            if (isset($scanResult['data']['attributes']['last_analysis_stats']['malicious']) &&
                $scanResult['data']['attributes']['last_analysis_stats']['malicious'] > 0) {
                throw new FileException(trans('general.virusDetected'), 403);
            }

        } catch (Exception $e) {
            Log::error('Error during virus scanning: ' . $e->getMessage());
            throw new Exception(trans('general.virusScanFailed'), 500);
        }

        // Generate a safe, random file name
        $fileName = Str::random(32);
        $extension = $file->getClientOriginalExtension(); // Safe way to get file extension
        $filePath = "Files/{$fileName}.{$extension}";

        // Store the file securely
        $path = Storage::disk('local')->putFileAs('Files', $file, $fileName . '.' . $extension);

        // Get the full URL path of the stored file
        $url = Storage::disk('local')->url($path);

        // احصل على الموديل المرتبط (Task أو أي موديل آخر) بناءً على attachable_id
        $attachable = Task::findOrFail($request->attachable_id); // افترض أن المرفقات مرتبطة بالـ Task

        // Store file metadata in the database using the Attachment model and morph relationship
        $attachment = Attachment::create([
            'attachable_id' => $attachable->id, // the ID of the task or any model you're attaching the file to
            'attachable_type' => get_class($attachable), // the type of the model (in this case, Task)
            'file_name' => $originalName,
            'file_path' => $url,
        ]);

        return $attachment;
    }
    /**
     * Summary of updateTaskStatus
     * @param array $data
     * @param int $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateTaskStatus(array $data, int $id)
{
        try {
            DB::beginTransaction();
            $task = Task::findOrFail($id);
            $oldStatus = $task->status;

            TaskStatusUpdate::create([
                'task_id' => $task->id,
                'old_status' => $oldStatus,
                'new_status' => $data['status']
            ]);


            $task->update(array_filter($data));

            DB::commit();

            return response()->json(['message' => 'Task status updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error while updating task status', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Summary of addComment
     * @param array $data
     * @param int $id
     * @throws \Exception
     * @return void
     */
    public function addComment(array $data, int $id)
    {
        try{
            DB::beginTransaction();
            $task = Task::findOrFail($id);
            $task->comments()->create($data);
            DB::commit();

        }catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    /**
     * Summary of restoreTask
     * @param mixed $id
     * @throws \Exception
     * @return void
     */
    public function restoreTask($id)
{
    try{
        $task = Task::withTrashed()->findOrFail($id);

        $task->restore();

    

    }catch (Exception $e) {
        Log::error($e->getMessage());
        throw new Exception($e->getMessage());
    }

}
}
