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
            'name'           => ['required', 'string', 'max:150'],
            'father_name'    => ['required', 'string', 'max:150'],
            'phone'          => ['required', 'string', 'max:40'],
            'email'          => ['nullable', 'email', 'max:190'],
            'address'        => ['nullable', 'string', 'max:255'],
            'stream'         => ['nullable', 'in:school,madarsa,both'],
            'class_no'       => ['nullable', 'integer', 'between:1,12'],
            'class_level'    => ['nullable', 'integer', 'between:1,8'],
            'section'        => ['nullable', 'string', 'max:8'],
            'madarsa_section'=> ['nullable', 'string', 'max:1'],
            'monthly_fee'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $stream = $data['stream'] ?? 'school';
        $cls    = (int) ($data['class_level'] ?? $data['class_no'] ?? 6);
        $msection = strtoupper($data['madarsa_section'] ?? 'A');
        $section = $msection;

        $plainPass = $request->input('password') ?: Student::defaultPassword($data['name']);

        $admission = Admission::create([
            'app_id'      => Admission::nextAppId(),
            'name'        => $data['name'],
            'father_name' => $data['father_name'],
            'phone'       => $data['phone'],
            'email'       => (string) ($data['email'] ?? ''),
            'address'     => (string) ($data['address'] ?? ''),
            'class_no'    => $cls,
            'section'     => $msection,
            'student_id'  => Student::nextStudentId(),
            'password'    => $plainPass,
            'status'      => 'Approved',
        ]);

        $plainPassword = $this->admit($admission, $notifier, $stream, $cls, $msection, $data['monthly_fee'] ?? null);

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
    private function admit(Admission $admission, UploadNotifier $notifier, string $stream = 'school', int $cls = 6, string $msection = 'A', $monthlyFee = null): string
    {
        $plainPassword = $admission->password;

        DB::transaction(function () use ($admission, $stream, $cls, $msection, $monthlyFee) {
            $admission->update(['status' => 'Approved']);

            $student = Student::create([
                'student_id'       => $admission->student_id,
                'name'             => $admission->name,
                'father_name'      => $admission->father_name,
                'phone'            => $admission->phone,
                'whatsapp'         => $admission->phone,
                'email'            => $admission->email,
                'address'          => $admission->address,
                'stream'           => $stream,
                'class_level'      => $stream === 'madarsa' ? null : $cls,
                'madarsa_section'  => $stream === 'school' ? null : $msection,
                'class_no'         => $cls,
                'section'          => $msection,
                'batch'            => 'Session ' . date('Y'),
                'admission_date'   => now()->toDateString(),
                'status'           => 'Active',
                'password'         => Hash::make($admission->password),
                'avatar_seed'      => $admission->name,
                'monthly_fee'      => ($monthlyFee !== null && $monthlyFee !== '') ? (float)$monthlyFee : Student::defaultFeeForClass($cls),
                'notifications_enabled' => true,
            ]);

            Fee::firstOrCreate(
                ['student_id' => $student->id, 'month' => date('Y-m')],
                [
                    'monthly_amount' => (int) ($student->monthly_fee ?? Student::defaultFeeForClass($cls)),
                    'paid_amount'    => 0,
                ]
            );

            Alert::create([
                'class_no' => $cls,
                'section'  => $msection,
                'msg'      => "Naya admission: {$admission->name} ({$student->display_class}) — ID {$admission->student_id}",
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
