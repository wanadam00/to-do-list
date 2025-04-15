<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class CheckSessionActivity
{
    public function handle($request, Closure $next)
    {
        $maxIdle = config('session.lifetime') * 60;

        if (Session::has('last_activity') &&
            (time() - Session::get('last_activity')) > $maxIdle) {

            $request->session()->invalidate();
            $request->session()->regenerate(); // Regenerate session ID
            $request->session()->regenerateToken();

            return redirect('/')->with('status', 'Session expired, TODO list cleared.');
        }

        Session::put('last_activity', time());

        return $next($request);
    }
}

