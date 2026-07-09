<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskOwner
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $task = $request->route('task');

        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You do not have permission to perform this action'
            ], 403);
        }

        return $next($request);
    }
}
