<?php

namespace App\Exceptions;

use Exception;

class CustomTaskException extends Exception
{
    public static function taskOverdue(): self
    {
        return new self('Задача уже просрочена.');
    }
    public static function reminderMustBeLater():self
    {
        return new self('Напоминание должно быть позже текущего времени');
    }

    public static function reminderMustBeLater15Min() : self
    {
        return new self('Напоминание должно быть позже минимум на 15 минут от текущего времени');
    }

    public static function taskCompleted(): self{
        return new self('Задача в статусе "completed"');
    }
}
