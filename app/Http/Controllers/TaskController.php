<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Models\TaskStatusUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ApiResponseService;
use App\Http\Requests\CommentCreateRequest;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
use App\Http\Requests\UpdateAssigneToRequest;
use App\Http\Requests\Task\AssigneTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Jobs\GenerateDailyTaskReport;
use App\Services\ReportService;

class TaskController extends Controller
{
    protected $taskService;

    /**
     * Summary of __construct
     * @param \App\Services\TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {

        $this->taskService = $taskService;

    }
    /**
     * Summary of index
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->only(['priority','status']);
        $perPage = $request->input('per_page', 15);
        $tasks = $this->taskService->listAllTask($filters,$perPage);
        return ApiResponseService::paginated($tasks,'tasks retrive success');
    }

    /**
     * Summary of store
     * @param \App\Http\Requests\Task\TaskStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TaskStoreRequest $request)
    {
      $data = $request->validated();
      $this->taskService->createTask($data);
      return ApiResponseService::success(null,'task created success');
    }

    /**
     * Summary of show
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task)
    {
        $this->taskService->showTask($task);
        return ApiResponseService::success($task,'task retrive success');
    }

    /**
     * Summary of update
     * @param \App\Http\Requests\TaskUpdateRequest $request
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TaskUpdateRequest $request, Task $task)
    {
        $data = $request->validated();
        $task = $this->taskService->updateTask($task,$data);
        return ApiResponseService::success($task,'task update success');
    }

    /**
     * Summary of destroy
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task)
    {
       $this->taskService->deleteTask($task);
       return ApiResponseService::success(null,'success');

    }

    /**
     * Summary of addAttachment
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAttachment(Request $request)
    {
        $this->taskService->addAttachment($request);
        return ApiResponseService::success(null,'success');
    }
    /**
     * Summary of updateTaskStatus
     * @param \App\Http\Requests\UpdateTaskStatusRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTaskStatus(UpdateTaskStatusRequest $request, int $id)
    {
        $data = $request->validated();
        $newStatus=$this->taskService->updateTaskStatus($data,$id);
        // Log::info("message");
         return ApiResponseService::success($newStatus,'success');
    }
    /**
     * Summary of reAssigne
     * @param int $id
     * @param \App\Http\Requests\UpdateAssigneToRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reAssigne(int $id,UpdateAssigneToRequest $request)
    {
        $data = $request->validated();
        $this->taskService->assigneTo($id,$data);
        return ApiResponseService::success(null,'success');
    }
    /**
     * Summary of addComment
     * @param \App\Http\Requests\CommentCreateRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(CommentCreateRequest $request, int $id)
    {
    $data = $request->validated();
    $this->taskService->addComment($data,$id);
    return ApiResponseService::success(null,'comment add success');

    }
    /**
     * Summary of generateDailyReport
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateDailyReport()
    {
       $report= GenerateDailyTaskReport::dispatch();
       return ApiResponseService::success($report);
    }

    public function restore(int $id)
    {
        $this->taskService->restoreTask($id);
        return ApiResponseService::success(null,'restore success');
    }



}
