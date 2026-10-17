<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Support\Sql;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $students = Student::where('status', 'Active')
            ->orderByRaw(Sql::numericSuffix('student_id'))
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

    /** QR scan page — camera se ID card scan kar ke attendance. */
    public function scanner()
    {
        $today = now()->toDateString();

        $scans = Attendance::where('date', $today)
            ->where('source', 'qr')
            ->with('student')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('admin.scan', [
            'today' => $today,
            'scans' => $scans,
        ]);
    }

    /** QR se aayi student_id ko Present mark karein (AJAX, JSON wapas). */
    public function scan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'string'],
        ]);

        $code    = strtoupper(trim($data['student_id']));
        $student = Student::where('student_id', $code)->where('status', 'Active')->first();

        if (!$student) {
            return response()->json([
                'ok'    => false,
                'error' => '"' . $code . '" ka koi active student nahi mila.',
            ], 404);
        }

        $today = now()->toDateString();
        $time  = now()->format('H:i:s');

        $row = Attendance::where('student_id', $student->id)
            ->where('date', $today)
            ->first();

        if ($row) {
            if ($row->status !== 'Present') {
                $row->update(['status' => 'Present', 'check_in_at' => $time, 'source' => 'qr']);
            }
            $already = true;
        } else {
            Attendance::create([
                'student_id'  => $student->id,
                'date'        => $today,
                'status'      => 'Present',
                'check_in_at' => $time,
                'source'      => 'qr',
            ]);
            $already = false;
        }

        return response()->json([
            'ok'         => true,
            'already'    => $already,
            'name'       => $student->name,
            'student_id' => $student->student_id,
            'class'      => 'Class ' . $student->class_no . '-' . $student->section,
            'time'       => now()->format('h:i A'),
            'message'    => $already
                ? $student->name . ' aaj pehle se Present tha.'
                : $student->name . ' — Present mark ho gaya!',
        ]);
    }
}
