<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        if ($request->session()->get('student_id')) {
            return redirect()->route('portal.show');
        }

        return view('auth.login', ['role' => $request->session()->get('login_role', 'admin')]);
    }

    public function login(Request $request): RedirectResponse
    {
        $role = $request->input('role') === 'student' ? 'student' : 'admin';
        $request->session()->put('login_role', $role);

        if ($role === 'admin') {
            $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $admin = Admin::where('email', $request->input('email'))->first();

            if (!$admin || !Hash::check($request->input('password'), $admin->password)) {
                return back()->withErrors([
                    'email' => 'Galat admin email ya password.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            $request->session()->forget('login_role');
            $request->session()->put('admin_id', $admin->id);

            return redirect()->route('admin.dashboard');
        }

        /* ---------- Student: ID + password (email se login nahi) ---------- */
        $request->validate([
            'student_id' => ['required', 'string'],
            'password'   => ['required', 'string'],
        ]);

        $code = strtoupper(trim($request->input('student_id')));
        $student = Student::where('student_id', $code)
            ->where('status', 'Active')
            ->first();

        if (!$student || !Hash::check($request->input('password'), $student->password)) {
            return back()->withErrors([
                'student_id' => 'Galat Student ID ya password.',
            ])->onlyInput('student_id');
        }

        $request->session()->regenerate();
        $request->session()->forget('login_role');
        $request->session()->put('student_id', $student->id);

        return redirect()->route('portal.show');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
