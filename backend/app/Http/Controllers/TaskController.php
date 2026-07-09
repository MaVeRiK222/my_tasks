<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected TaskService $taskService;
    
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function getTaskById($task_id)
    {
//        return $this->taskService->getById($task_id);
        return;
    }

    public function getAllTasksByUserId()
    {
        $user_id = Auth::id();
        $tasks = $this->taskService->getAllTasksByUserId($user_id);
        return response()->json($tasks, 200);
    }

    public function createTask(StoreTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $task = $this->taskService->store($validated);
        return response()->json($task, 201);
    }

    public function updateTask(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();
        $task = $this->taskService->update($task, $validated);
        return response()->json($task, 200);
    }

    public function deleteTask(Task $task)
    {
        $this->taskService->delete($task);
    }

    public function createOrUpdateTaskReminder($user_id)
    {

    }

    public function deleteTaskReminder($user_id)
    {

    }
}
