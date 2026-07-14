<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\TaskStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function reminders(): HasManyThrough
    {
        return $this->hasManyThrough(
            Reminder::class,
            Task::class,
            'user_id',
            'task_id',
            'id',
            'id'
        );
    }

    public function getActiveRemindersCount(): int
    {
        return $this->reminders()
            ->where('reminders.reminder_at', '>=', now())
            ->where('tasks.status', '=', TaskStatusEnum::PENDING->value)
            ->count();
    }

    public function hasReachedActiveReminderLimit(): bool
    {
        return $this->getActiveRemindersCount() >= config('settings.reminders.limit');
    }
}
