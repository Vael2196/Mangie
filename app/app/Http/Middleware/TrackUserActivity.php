<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        // Passive polling, fragment hydration and broadcasting auth do not imply activity.
        $passive = $request->is(
            'broadcasting/*', 'realtime/*', 'presence/*', 'boards/*/presence',
            'tasks/*/cover', 'tasks/*/fragment/*', 'columns/*/fragment', 'boards/*/fragment/*'
        ) || str_contains((string) $request->header('Sec-Purpose'), 'prefetch');
        // Uploading/removing a cover is a real action; loading its image is not.
        if ($request->is('tasks/*/cover') && ! $request->isMethod('GET')) {
            $passive = false;
        }
        $active = ! $passive && (! $request->expectsJson() || ! $request->isMethod('GET'));
        if ($active) {
            $this->touch($request);
        }
        $response = $next($request);
        if ($active) {
            $this->touch($request);
        } // Also captures successful sign-in.

        return $response;
    }

    private function touch(Request $request): void
    {
        $user = $request->user();
        if (! $user || ($user->last_active_at && $user->last_active_at->gt(now()->subMinute()))) {
            return;
        }
        DB::table('users')->where('id', $user->id)->update(['last_active_at' => now()]);
        $user->last_active_at = now();
    }
}
