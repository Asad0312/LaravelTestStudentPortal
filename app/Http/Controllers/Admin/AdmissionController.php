<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Alert;
use App\Models\Fee;
use App\Models\Student;
use App\Services\UploadNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdmissionController extends Controller
{
    public function index()
    {
        return view('admin.admissions', [
            'rows'    => Admission::orderBy('id', 'desc')->get(),
            'pending' => Admission::where('status', 'Pending')->count(),
        ]);
    }

    /**
     * Admin khud admission form bharta hai — submit karte hi student register ho jata hai.
     */
    public function store(Request $request, UploadNotifier $notifier)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'father_name' => ['required', 'string', 'max:150'],
            'phone'       => ['required', 'string', 'max:40'],
            'email'       => ['nullable', 'email', 'max:190'],
            'address'     => ['nullable', 'string', 'max:255'],
            'class_no'    => ['required', 'integer', 'between:1,12'],
            'section'     => ['nullable', 'string', 'max:8'],
        ]);

        $admission = Admission::create([
            'app_id'      => Admission::nextAppId(),
            'name'        => $data['name'],
            'father_name' => $data['father_name'],
            'phone'       => $data['phone'],
            'email'       => (string) ($data['email'] ?? ''),
            'address'     => (string) ($data['address'] ?? ''),
            'class_no'    => (int) $data['class_no'],
            'section'     => (string) ($data['section'] ?: 'A'),
            'student_id'  => Student::nextStudentId(),
            'password'    => Student::defaultPassword($data['name']),
            'status'      => 'Approved',
        ]);

        $plainPassword = $this->admit($admission, $notifier);

        return back()->with(
            'success',
            $admission->name . ' ka admission ho gaya. Student ID: ' . $admission->student_id
            . ' • Password: ' . $plainPassword . ' — ye ID/password student ko dein.'
        );
    }

    public function approve(Request $request, int $id, UploadNotifier $notifier)
    {
        $admission = Admission::find($id);
        if (!$admission) {
            return back()->with('error', 'Admission request nahi mili.');
        }

        if ($admission->status === 'Approved') {
            return back()->with('success', $admission->name . ' pehle se registered hai. Student ID: ' . $admission->student_id);
        }

        $plainPassword = $this->admit($admission, $notifier);

        return back()->with(
            'success',
            $admission->name . ' ab registered hai. Student ID: ' . $admission->student_id . ' • Password: ' . $plainPassword
        );
    }

    public function reject(Request $request, int $id)
    {
        $admission = Admission::find($id);
        if (!$admission) {
            return back()->with('error', 'Admission request nahi mili.');
        }

        $admission->update(['status' => 'Rejected']);

        return back()->with('success', 'Request reject ho gayi.');
    }

    /**
     * Admission record ko student me convert karta hai (fee + alert + notification ke saath).
     * @return string plain password (student ko dene ke liye)
     */
    private function admit(Admission $admission, UploadNotifier $notifier): string
    {
        $plainPassword = $admission->password;

        DB::transaction(function () use ($admission) {
            $admission->update(['status' => 'Approved']);

            $student = Student::create([
                'student_id'     => $admission->student_id,
                'name'           => $admission->name,
                'father_name'    => $admission->father_name,
                'phone'          => $admission->phone,
                'email'          => $admission->email,
                'address'        => $admission->address,
                'class_no'       => (int) $admission->class_no,
                'section'        => $admission->section,
                'batch'          => 'Session ' . date('Y'),
                'admission_date' => now()->toDateString(),
                'status'         => 'Active',
                'password'       => Hash::make($admission->password),
                'avatar_seed'    => $admission->name,
            ]);

            Fee::firstOrCreate(
                ['student_id' => $student->id, 'month' => date('Y-m')],
                [
                    'monthly_amount' => Student::defaultFeeForClass((int) $admission->class_no),
                    'paid_amount'    => 0,
                ]
            );

            Alert::create([
                'class_no' => (int) $admission->class_no,
                'section'  => $admission->section,
                'msg'      => "Naya admission: {$admission->name} (ID {$admission->student_id}) joined Class {$admission->class_no}-{$admission->section}",
            ]);
        });

        $notifier->notify(
            'Admission — ' . $admission->name,
            "{$admission->name} registered hai.\nStudent ID: {$admission->student_id}\nPassword: {$plainPassword}",
            (int) $admission->class_no,
            $admission->section
        );

        return $plainPassword;
    }
}
