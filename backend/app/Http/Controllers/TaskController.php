<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomTaskException;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\ReminderService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    protected TaskService $taskService;
    protected ReminderService $reminderService;

    public function __construct(TaskService $taskService, ReminderService $reminderService)
    {
        $this->taskService = $taskService;
        $this->reminderService = $reminderService;
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

    public function createOrUpdateTaskReminder(Task $task, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reminder_at' => ['required', Rule::date()->format('Y-m-d H:i:s'), 'after:now']
        ]);
        if ($this->taskService->hasReminder($task)) {
            if ($this->taskService->isTaskOverdue($task)) {
                throw CustomTaskException::taskOverdue();
            }
            $reminder = $task->reminders()->first();
            $reminder = $this->reminderService->update($reminder, $validated);
            return response()->json($reminder, 200);
        } else {
            if ($task->user->hasReachedActiveReminderLimit()) {
                throw CustomTaskException::reminderLimitReached();
            }
            $validated['task_id'] = $task->id;
            $reminder = $this->reminderService->create($validated, $request->user()->id);
            return response()->json($reminder, 201);

        }
    }

    public function deleteTaskReminder(Task $task)
    {
        $reminder = $task->reminders()->firstOrFail();
        $this->reminderService->delete($reminder);
    }


}
