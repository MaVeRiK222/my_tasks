<?php

namespace App\Services;

use App\Enums\TaskStatusEnum;
use App\Exceptions\CustomTaskException;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaskService
{
    protected $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    public function getAllTasksByUserId($user_id)
    {
        return Task::where('user_id', '=', $user_id)->get();
    }

    public function store($task_data): Task
    {
        if (!empty($task_data['reminder_at'])) {
            return $this->storeWithReminder($task_data);
        }

        return Task::create($task_data);
    }

    public function storeWithReminder($task_data): Task
    {
        $this->validateStoreWithReminder($task_data);
        return DB::transaction(function () use ($task_data) {
            $task = Task::create($task_data);
            $this->reminderService->create([
                'task_id' => $task->id,
                'reminder_at' => $task_data['reminder_at']
            ], $task_data['user_id']);
            return $task;
        });
    }


    public function update(Task $task, $data): Task
    {

        $lock = Cache::lock("update_task_{$task->id}", 30);

        if (!$lock->get()) {
            throw CustomTaskException::conflict409();
        }


        try {
            $this->validateTaskUpdate($task, $data);
            return DB::transaction(function () use ($task, $data) {
                if (!empty($data['reminder_at'])) {
                    $reminder = $this->reminderService->getReminderByTask($task);
                    if (!empty($reminder)) {
                        $this->reminderService->update($reminder, ['reminder_at' => $data['reminder_at']]);
                    } else {
                        $this->reminderService->create([
                            'task_id' => $task->id,
                            'reminder_at' => $data['reminder_at']
                        ], $task->user_id);
                    }
                } else if (array_key_exists('reminder_at', $data)) {
                    if (is_null($data['reminder_at'])) {
                        $task->reminders()->delete();
                    }
                } else if (!empty($data['status']) && TaskStatusEnum::tryFrom($data['status']) === TaskStatusEnum::COMPLETED) {
                    $task->reminders()->delete();
                }

                $task->fill($data);
                if ($task->isDirty()) {
                    $task->save();
                }
                return $task;
            }, 3);
        } finally {
            $lock->release();
        }
    }

    public function delete($task): void
    {
        $task->delete();
    }

    public function hasReminder(Task $task): bool
    {
        return $task->reminders()->exists();
    }

    public function isTaskOverdue(Task $task): bool
    {
        $reminder_at = $task->reminders()->first()?->reminder_at;
        if (!empty($reminder_at)) {
            $date = Carbon::parse($reminder_at);
            $minDate = now();
            return $date->lessThan($minDate);
        }
        return false;
    }


    public function validateStoreWithReminder($task_data): void
    {
        $date = Carbon::parse($task_data['reminder_at']);
        $minDate = now()->addMinutes(15);
        if ($date->lessThan($minDate)) {
            throw CustomTaskException::reminderMustBeLater15Min();
        }
        $user = User::findOrFail($task_data['user_id']);
        if ($user->hasReachedActiveReminderLimit()) {
            throw CustomTaskException::reminderLimitReached();
        }
    }

    public function validateTaskUpdate(Task $task, $data): void
    {
        $minDate = now();

        if (!empty($data['reminder_at'])) {
            if ($task->isCompleted()) {
                throw CustomTaskException::taskCompleted();
            }

            $reminder_at = $task->reminders()->first()?->reminder_at;
            if (!empty($reminder_at)) {
                $task_reminder_at = Carbon::parse($reminder_at);
                if ($task_reminder_at->lessThan($minDate)) {
                    throw CustomTaskException::taskOverdue();
                }
            }

            $request_reminder_at_date = Carbon::parse($data['reminder_at']);
            if ($request_reminder_at_date->lessThan($minDate)) {
                throw CustomTaskException::reminderMustBeLater();
            }
        }
    }
}
