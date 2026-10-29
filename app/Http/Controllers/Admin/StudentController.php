<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\Student;
use App\Services\UploadNotifier;
use App\Support\Sql;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $students = Student::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('student_id', 'like', "%{$q}%")
                        ->orWhere('section', 'like', "%{$q}%")
                        ->orWhere('class_no', 'like', "%{$q}%");
                });
            })
            ->orderByRaw(Sql::numericSuffix('student_id'))
            ->get();

        return view('admin.students', [
            'students' => $students,
            'q'        => $q,
            'nextId'   => Student::nextStudentId(),
        ]);
    }

    public function nextId()
    {
        return response()->json(['next_id' => Student::nextStudentId()]);
    }

    public function store(Request $request, UploadNotifier $notifier)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'father_name'    => ['nullable', 'string', 'max:150'],
            'phone'          => ['nullable', 'string', 'max:40'],
            'email'          => ['nullable', 'email', 'max:190'],
            'address'        => ['nullable', 'string', 'max:255'],
            'stream'         => ['nullable', 'in:school,madarsa,both'],
            'class_no'       => ['nullable', 'integer', 'between:1,12'],
            'class_level'    => ['nullable', 'integer', 'between:1,8'],
            'section'        => ['nullable', 'string', 'max:8'],
            'madarsa_section'=> ['nullable', 'string', 'max:1'],
            'monthly_fee'    => ['nullable', 'numeric', 'min:0'],
            'status'         => ['nullable', 'string', 'max:20'],
        ]);

        $plainPassword = $request->input('password')
            ?: Student::defaultPassword($data['name']);
        $stream = $data['stream'] ?? 'school';
        $cls    = (int) ($data['class_level'] ?? $data['class_no'] ?? 6);
        $msection = strtoupper($data['madarsa_section'] ?? 'A');
        $section = $msection;

        DB::beginTransaction();
        try {
            $student = Student::create([
                'student_id'       => Student::nextStudentId(),
                'name'             => $data['name'],
                'father_name'      => (string) ($data['father_name'] ?? ''),
                'phone'            => (string) ($data['phone'] ?? ''),
                'whatsapp'         => (string) ($data['phone'] ?? ''),
                'email'            => (string) ($data['email'] ?? ''),
                'address'          => (string) ($data['address'] ?? ''),
                'stream'           => $stream,
                'class_level'      => $stream === 'madarsa' ? null : $cls,
                'madarsa_section'  => $stream === 'school' ? null : $msection,
                'class_no'         => $cls,
                'section'          => $section,
                'batch'            => 'Session ' . date('Y'),
                'admission_date'   => now()->toDateString(),
                'status'           => $data['status'] ?? 'Active',
                'password'         => Hash::make($plainPassword),
                'avatar_seed'      => $data['name'],
                'monthly_fee'      => $data['monthly_fee'] ?? Student::defaultFeeForClass($cls),
                'notifications_enabled' => true,
            ]);

            Fee::firstOrCreate(
                ['student_id' => $student->id, 'month' => date('Y-m')],
                ['monthly_amount' => (int) ($student->monthly_fee ?? Student::defaultFeeForClass($cls)), 'paid_amount' => 0]
            );

            Alert::create([
                'class_no' => $cls,
                'section'  => $section,
                'msg'      => "New student registered: {$student->name} ({$student->display_class}) — ID {$student->student_id}",
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $notifier->notify(
            'New student registered',
            "{$student->name} (ID: {$student->student_id}) — Class {$cls}-{$section}\nPortal password: {$plainPassword}",
            $cls,
            $section
        );

        return back()->with('success', "Student add ho gaya. Student ID: {$student->student_id} • Password: {$plainPassword}");
    }

    public function update(Request $request, int $id)
    {
        $student = Student::find($id);
        if (!$student) {
            return back()->with('error', 'Student nahi mila.');
        }

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'father_name'    => ['nullable', 'string', 'max:150'],
            'phone'          => ['nullable', 'string', 'max:40'],
            'email'          => ['nullable', 'email', 'max:190'],
            'address'        => ['nullable', 'string', 'max:255'],
            'stream'         => ['nullable', 'in:school,madarsa,both'],
            'class_level'    => ['nullable', 'integer', 'between:1,8'],
            'class_no'       => ['nullable', 'integer', 'between:1,12'],
            'madarsa_section'=> ['nullable', 'string', 'max:1'],
            'section'        => ['nullable', 'string', 'max:8'],
            'batch'          => ['nullable', 'string', 'max:40'],
            'status'         => ['nullable', 'string', 'max:20'],
            'password'       => ['nullable', 'string', 'min:4', 'max:60'],
            'monthly_fee'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $stream = $data['stream'] ?? $student->stream ?? 'school';
        $cls    = (int) ($data['class_level'] ?? $data['class_no'] ?? $student->class_level ?? $student->class_no ?? 6);
        $msec   = strtoupper($data['madarsa_section'] ?? $data['section'] ?? $student->madarsa_section ?? $student->section ?? 'A');

        $update = [
            'name'           => $data['name'],
            'father_name'    => (string) ($data['father_name'] ?? ''),
            'phone'          => (string) ($data['phone'] ?? ''),
            'whatsapp'       => (string) ($data['phone'] ?? $student->whatsapp ?? ''),
            'email'          => (string) ($data['email'] ?? ''),
            'address'        => (string) ($data['address'] ?? ''),
            'stream'         => $stream,
            'class_level'    => $stream === 'madarsa' ? null : $cls,
            'madarsa_section'=> $stream === 'school' ? null : $msec,
            'class_no'       => $cls,
            'section'        => $msec,
            'batch'          => (string) ($data['batch'] ?? $student->batch ?? ('Session ' . date('Y'))),
            'status'         => $data['status'] ?? $student->status ?? 'Active',
            'avatar_seed'    => $data['name'],
        ];

        if (array_key_exists('monthly_fee', $data) && $data['monthly_fee'] !== null && $data['monthly_fee'] !== '') {
            $update['monthly_fee'] = (float) $data['monthly_fee'];
        }

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $student->update($update);

        return back()->with('success', $student->name . ' update ho gaya.');
    }

    public function destroy(int $id)
    {
        $student = Student::find($id);
        if (!$student) {
            return back()->with('error', 'Student nahi mila.');
        }

        DB::transaction(function () use ($student) {
            Mark::where('student_id', $student->id)->delete();
            Attendance::where('student_id', $student->id)->delete();
            Payment::where('student_id', $student->id)->delete();
            Fee::where('student_id', $student->id)->delete();
            $student->delete();
        });

        return back()->with('success', 'Student delete ho gaya.');
    }
}
