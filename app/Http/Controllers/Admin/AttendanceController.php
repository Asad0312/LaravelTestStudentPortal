<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $students = Student::where('status', 'Active')
            ->orderByRaw("CAST(SUBSTRING_INDEX(student_id, '-', -1) AS UNSIGNED)")
            ->get();

        $marked = Attendance::where('date', $today)
            ->get()
            ->keyBy('student_id');

        $present = $marked->where('status', 'Present')->count();

        return view('admin.attendance', [
            'students' => $students,
            'marked'   => $marked,
            'today'    => $today,
            'present'  => $present,
            'absent'   => max(0, $students->count() - $present),
            'rate'     => $students->count() > 0 ? round(($present / $students->count()) * 100, 1) : 0,
        ]);
    }

    public function mark(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'string'],
            'status'     => ['required', 'in:Present,Absent'],
        ]);

        $code   = strtoupper(trim($data['student_id']));
        $student = Student::where('student_id', $code)->first();

        if (!$student) {
            return back()->with('error', '"' . $code . '" ka koi record nahi mila.');
        }

        $exists = Attendance::where('student_id', $student->id)
            ->where('date', now()->toDateString())
            ->first();

        if ($exists) {
            $exists->update([
                'status'      => $data['status'],
                'check_in_at' => now()->format('H:i:s'),
            ]);

            return back()->with('success', $student->name . ' — ' . $data['status'] . ' (update ho gaya)');
        }

        Attendance::create([
            'student_id'  => $student->id,
            'date'        => now()->toDateString(),
            'status'      => $data['status'],
            'check_in_at' => $data['status'] === 'Present' ? now()->format('H:i:s') : null,
            'source'      => 'manual',
        ]);

        return back()->with('success', $student->name . ' (' . $student->student_id . ') — ' . $data['status']);
    }
}
