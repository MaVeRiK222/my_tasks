<?php

namespace App\Models;

use App\Enums\TaskStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Task extends Model
{
    use HasFactory;
    //
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
    ];
    protected $casts = [
        'status' => TaskStatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reminders(): HasOne
    {
        return $this->hasOne(Reminder::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatusEnum::COMPLETED;
    }
}
