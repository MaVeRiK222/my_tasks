<?php

namespace App\Exceptions;

use Exception;

class CustomTaskException extends Exception
{
    public static function taskOverdue(): self
    {
        return new self('Задача уже просрочена.', 400);
    }

    public static function reminderMustBeLater(): self
    {
        return new self('Напоминание должно быть позже текущего времени', 400);
    }

    public static function reminderMustBeLater15Min(): self
    {
        return new self('Напоминание должно быть позже минимум на 15 минут от текущего времени', 400);
    }

    public static function taskCompleted(): self
    {
        return new self('Задача в статусе "completed"', 400);
    }

    public static function reminderLimitReached(): self
    {
        return new self('У пользователя может быть максимум 3 активных напоминания', 400);
    }

    public static function conflict409(): self
    {
        return new self('Конкурирующий запрос', 409);
    }

}
