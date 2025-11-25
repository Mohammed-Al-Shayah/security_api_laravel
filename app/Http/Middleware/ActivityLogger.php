<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;

class ActivityLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // نمرّر الطلب ونأخذ الـ response
        $response = $next($request);

        // 👈 لو المستخدم مش مسجّل دخول (login) ما نعملش Log
        $user = $request->user(); // sanctum / default guard
        if (! $user) {
            return $response;
        }

        // 📝 نحاول نخفّي أي بيانات حساسة من الـ payload
        $payload = $request->except([
            'password',
            'password_confirmation',
            'current_password',
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action'  => $request->method().' '.$request->route()?->getName(),
            'url'     => $request->path(),
            'ip'      => $request->ip(),
            'payload' => $payload ? json_encode($payload) : null,
        ]);

        return $response;
    }
}
