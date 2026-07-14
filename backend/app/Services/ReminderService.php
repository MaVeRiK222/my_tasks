<?php

namespace App\Services;

use App\Exceptions\CustomTaskException;
use App\Models\Reminder;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class ReminderService
{
    public function getReminderByTask(Task $task): ?Reminder
    {
        return $task->reminders()->first();
    }

    public function create($data, $user_id): Reminder
    {
        $lock = Cache::lock(config('settings.lock_key.create_reminder') . $user_id, 30);

        if (!$lock->get()) {
            throw CustomTaskException::conflict409();
        }
        try {
            return Reminder::create($data);
        } finally {
            $lock->release();
        }
    }

    public function update(Reminder $old_reminder, $data): Reminder
    {
        $old_reminder->fill($data);
        if ($old_reminder->isDirty()) {
            $old_reminder->save();
        }
        return $old_reminder;

    }

    public function delete(Reminder $reminder)
    {
        $reminder->delete();
    }

}
