<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Message;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Test;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $student = null;
        $data = [];

        if ($q !== '') {
            $student = Student::where('student_id', $q)
                ->orWhere('name', 'like', '%' . $q . '%')
                ->orWhere('phone', $q)
                ->orderByRaw("CASE WHEN student_id = ? THEN 0 ELSE 1 END", [$q])
                ->first();

            if ($student) {
                $total   = Attendance::where('student_id', $student->id)->count();
                $present = Attendance::where('student_id', $student->id)->where('status', 'Present')->count();

                $data = [
                    'attendance' => [
                        'total'   => $total,
                        'present' => $present,
                        'percent' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                        'recent'  => Attendance::where('student_id', $student->id)->orderByDesc('date')->limit(10)->get(),
                    ],
                    'fees'     => $student->fees()->orderByDesc('month')->get(),
                    'payments' => $student->payments()->orderByDesc('paid_on')->get(),
                    'tests'    => Test::where('class_no', $student->class_no)->where('section', $student->section)->orderByDesc('test_date')->get(),
                    'marks'    => $student->marks()->with('test')->get(),
                    'messages' => Message::forStudent($student)->orderByDesc('id')->limit(5)->get(),
                    'classInfo'=> SchoolClass::where('class_no', $student->class_no)->where('section', $student->section)->first(),
                ];
            }
        }

        return view('admin.lookup', [
            'q'       => $q,
            'student' => $student,
            'data'    => $data,
            'recent'  => Student::orderByDesc('id')->limit(6)->get(),
        ]);
    }
}
