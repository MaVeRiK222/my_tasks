<?php

namespace App\Services;

use App\Enums\TaskStatusEnum;
use App\Exceptions\CustomTaskException;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TaskService
{

    public function getById($task_id)
    {
        return Task::findOrFail($task_id)->toArray();
    }

    public function getAllTasksByUserId($user_id): Task
    {
        return Task::where('user_id', '=', $user_id);
    }

    public function store($task_data): Task
    {
        $date = Carbon::parse($task_data['reminder_at']);
        $minDate = now()->addMinutes(15);


        if ($date->lessThan($minDate)) {
            throw CustomTaskException::reminderMustBeLater15Min();
        }
        return Task::create($task_data);
    }

    public function update(Task $task, $data): Task
    {
        $minDate = now();

        if (!empty($data['reminder_at'])) {

            if ($task->status == TaskStatusEnum::COMPLETED) {
                throw CustomTaskException::taskCompleted();
            }

            if (!empty($task->reminder_at)) {
                $task_reminder_at = Carbon::parse($task->reminder_at);
                if ($task_reminder_at->lessThan($minDate)) {
                    throw CustomTaskException::taskOverdue();
                }
            }

            $request_reminder_at_date = Carbon::parse($data['reminder_at']);
            if ($request_reminder_at_date->lessThan($minDate)) {
                throw CustomTaskException::reminderMustBeLater();
            }
        }


        $task->fill($data);
        if ($task->isDirty()) {
            $task->save();
        }
        return $task;
    }

    public function delete($task): void
    {
        $task->delete();
    }

    public function createReminder($task_id)
    {
    }

    public function deleteReminder($task_id)
    {
    }
}
