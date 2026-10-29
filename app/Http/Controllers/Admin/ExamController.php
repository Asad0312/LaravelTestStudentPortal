<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamEvent;
use App\Models\ExamFee;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index()
    {
        $events = ExamEvent::with(['fees.student'])->latest('id')->get();
        return view('admin.exams', ['events' => $events]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'exam_month' => ['required', 'date'],
            'stream' => ['required', 'in:school,madarsa,both'],
            'class_level' => ['nullable', 'integer', 'between:1,8'],
            'madarsa_section' => ['nullable', 'string', 'max:1'],
            'exam_fee' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            $event = ExamEvent::create([
                'title' => $data['title'],
                'exam_month' => $data['exam_month'],
                'stream' => $data['stream'],
                'class_level' => $data['stream'] === 'madarsa' ? null : ($data['class_level'] ?? null),
                'madarsa_section' => $data['stream'] === 'school' ? null : strtoupper($data['madarsa_section'] ?? 'A'),
                'exam_fee' => $data['exam_fee'],
                'created_by' => session('admin_id'),
            ]);

            $students = Student::where('status', 'Active')->get()->filter(function ($s) use ($event) {
                if ($event->stream === 'school') {
                    if ($s->stream === 'madarsa') return false;
                    if ($event->class_level === null) return true;
                    return ($s->class_level ?? $s->class_no) == $event->class_level;
                }
                if ($event->stream === 'madarsa') {
                    if ($s->stream === 'school') return false;
                    if ($event->madarsa_section === null) return true;
                    return strtoupper($s->madarsa_section ?? $s->section) == $event->madarsa_section;
                }
                // both
                if ($event->class_level !== null && $s->stream !== 'madarsa') {
                    if (($s->class_level ?? $s->class_no) != $event->class_level && $s->stream !== 'both') return false;
                }
                if ($event->madarsa_section !== null && $s->stream !== 'school') {
                    if (strtoupper($s->madarsa_section ?? $s->section) != $event->madarsa_section && $s->stream !== 'both') return false;
                }
                if ($event->class_level === null && $event->madarsa_section === null) return true;
                $ok = false;
                if ($s->stream !== 'madarsa' && ($event->class_level === null || ($s->class_level ?? $s->class_no) == $event->class_level)) $ok = true;
                if (!$ok && $s->stream !== 'school' && ($event->madarsa_section === null || strtoupper($s->madarsa_section ?? $s->section) == $event->madarsa_section)) $ok = true;
                return $ok;
            });

            foreach ($students as $st) {
                ExamFee::firstOrCreate([
                    'exam_event_id' => $event->id,
                    'student_id' => $st->id,
                ], [
                    'amount' => $event->exam_fee,
                    'status' => 'unpaid',
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return back()->with('success', 'Exam announced. Exam fees generated.');
    }

    public function collect(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'string'],
            'exam_event_id' => ['required', 'exists:exam_events,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $student = Student::where('student_id', $data['student_id'])->firstOrFail();
        $fee = ExamFee::where('exam_event_id', $data['exam_event_id'])->where('student_id', $student->id)->firstOrFail();
        $fee->update([
            'amount' => $data['amount'],
            'status' => 'paid',
            'paid_on' => now()->toDateString(),
            'received_by' => session('admin_id'),
        ]);

        return back()->with('success', $student->name . ' ('.$student->student_id.') ka exam fee Rs. '.number_format($data['amount']).' collect ho gaya.');
    }

    public function markPaid(Request $request)
    {
        $data = $request->validate([
            'exam_fee_id' => ['required', 'exists:exam_fees,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $fee = ExamFee::with('student','event')->findOrFail($data['exam_fee_id']);
        $amt = $data['amount'] !== null && $data['amount'] !== '' ? $data['amount'] : $fee->amount;
        $fee->update([
            'amount' => $amt,
            'status' => 'paid',
            'paid_on' => now()->toDateString(),
            'received_by' => session('admin_id'),
        ]);

        return back()->with('success', ($fee->student->name ?? '') . ' ka exam fee paid mark ho gaya.');
    }
}
