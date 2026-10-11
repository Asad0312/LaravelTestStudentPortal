<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty($request->session()->get('admin_id'))) {
            // Student login hai to usse admin panel bilkul na dikhe
            if (!empty($request->session()->get('student_id'))) {
                return redirect()->route('portal.show');
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
