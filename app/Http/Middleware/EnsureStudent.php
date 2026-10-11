<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty($request->session()->get('student_id'))) {
            if (!empty($request->session()->get('admin_id'))) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
