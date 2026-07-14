<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reminder extends Model
{
    use HasFactory;
    //
    protected $fillable = [
        'task_id',
        'reminder_at'
    ];

    protected $casts = [
        'reminder_at' => 'datetime',
    ];

    public function tasks(): HasOne
    {
        return $this->hasOne(Task::class);
    }
}
