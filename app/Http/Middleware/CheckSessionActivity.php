<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class CheckSessionActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if session has last activity time
        if (Session::has('last_activity')) {
            $sessionLifetime = config('session.lifetime') * 60; // Convert to seconds

            // Check if session has expired due to inactivity
            if (time() - Session::get('last_activity') > $sessionLifetime) {
                Session::flush();
                return redirect()->refresh()->with('message', 'Session expired due to inactivity');
            }
        }

        // Update last activity time
        Session::put('last_activity', time());

        return $next($request);
    }
}
