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
            $was = $exists->status;
            $exists->update([
                'status'      => $data['status'],
                'check_in_at' => $data['status'] === 'Present' ? now()->format('H:i:s') : $exists->check_in_at,
            ]);

            if ($was === $data['status']) {
                return back()->with('warning', $student->name . ' (' . $student->student_id . ') — aaj pehle se ' . $data['status'] . ' mark hai.');
            }

            return back()->with('success', $student->name . ' (' . $student->student_id . ') — ' . $was . ' se ' . $data['status'] . ' me update ho gaya.');
        }

        Attendance::create([
            'student_id'  => $student->id,
            'date'        => now()->toDateString(),
            'status'      => $data['status'],
            'check_in_at' => $data['status'] === 'Present' ? now()->format('H:i:s') : null,
            'source'      => 'manual',
        ]);

        return back()->with('success', $student->name . ' (' . $student->student_id . ') — ' . $data['status'] . ' mark ho gaya.');
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
                $already = false;
                $updated = true;
            } else {
                $already = true;
                $updated = false;
            }
        } else {
            Attendance::create([
                'student_id'  => $student->id,
                'date'        => $today,
                'status'      => 'Present',
                'check_in_at' => $time,
                'source'      => 'qr',
            ]);
            $already = false;
            $updated = false;
        }

        $cls = method_exists($student, 'getDisplayClassAttribute') ? $student->display_class : ('Class ' . ($student->class_level ?? $student->class_no) . '-' . ($student->madarsa_section ?? $student->section));

        return response()->json([
            'ok'         => true,
            'already'    => $already,
            'updated'    => $updated ?? false,
            'name'       => $student->name,
            'student_id' => $student->student_id,
            'class'      => $cls,
            'time'       => now()->format('h:i A'),
            'message'    => $already
                ? $student->name . ' (' . $student->student_id . ') — aaj pehle se Present mark hai.'
                : (($updated ?? false) ? $student->name . ' (' . $student->student_id . ') — Absent se Present me update ho gaya.' : $student->name . ' (' . $student->student_id . ') — Present mark ho gaya!'),
        ]);
    }
}
