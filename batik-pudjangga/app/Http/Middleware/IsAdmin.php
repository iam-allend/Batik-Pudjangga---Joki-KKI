<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is admin
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to access admin area.');
        }

        // Check if user has admin role
        // Option 1: Using simple flag on users table
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        // Option 2: Using role column on users table
        // if (auth()->user()->role !== 'admin') {
        //     abort(403, 'Unauthorized action.');
        // }

        return $next($request);
    }
}
