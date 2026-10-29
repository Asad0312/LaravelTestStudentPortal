<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Alert;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\ExamFee;
use App\Models\Fee;
use App\Models\Mark;
use App\Models\Message;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Test;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PortalController extends Controller
{
    public function show(Request $request)
    {
        $student = Student::find($request->session()->get('student_id'));
        if (!$student) {
            $request->session()->flush();

            return redirect()->route('login');
        }

        /* attendance summary */
        $attRows = Attendance::where('student_id', $student->id)->orderByDesc('date')->limit(7)->get();
        $total   = Attendance::where('student_id', $student->id)->count();
        $present = Attendance::where('student_id', $student->id)->where('status', 'Present')->count();

        $week = $attRows->sortBy('date')->map(fn ($a) => $a->status === 'Present' ? 1 : 0)->values()->all();
        $week = array_pad($week, 7, 0);

        /* fee */
        $feeRow = $student->fees()->orderByDesc('month')->first();
        if ($feeRow && $student->monthly_fee && (int)$feeRow->monthly_amount !== (int)$student->monthly_fee) {
            $feeRow->monthly_amount = (int)$student->monthly_fee;
            $feeRow->save();
        } elseif (!$feeRow && $student->monthly_fee) {
            $feeRow = Fee::firstOrCreate(
                ['student_id' => $student->id, 'month' => date('Y-m')],
                ['monthly_amount' => (int)$student->monthly_fee, 'paid_amount' => 0]
            );
        }
        $monthly = (int) ($feeRow->monthly_amount ?? $student->monthly_fee ?? Student::defaultFeeForClass((int) ($student->class_level ?? $student->class_no)));
        $paid    = (int) ($feeRow->paid_amount ?? 0);
        $rem     = max(0, $monthly - $paid);
        $feeStatus = $rem <= 0 ? 'Paid' : ($paid > 0 ? 'Pending' : 'Unpaid');
        $feePct    = $monthly > 0 ? round(($paid / $monthly) * 100) : 0;

        /* academics */
        $stream = $student->stream ?? 'school';
        $sCls = $student->class_level ?? $student->class_no;
        $sSec = strtoupper($student->madarsa_section ?? $student->section ?? 'A');

        $tests = Test::where(function($q) use ($stream, $sCls, $sSec) {
            if ($stream === 'school') {
                $q->where(function($qq){ $qq->where('stream','school')->orWhere('stream','both'); });
                $q->where(function($qq) use ($sCls){ $qq->whereNull('class_level')->orWhere('class_level', $sCls); });
            } elseif ($stream === 'madarsa') {
                $q->where(function($qq){ $qq->where('stream','madarsa')->orWhere('stream','both'); });
                $q->where(function($qq) use ($sSec){ $qq->whereNull('madarsa_section')->orWhere('madarsa_section', $sSec); });
            } else {
                $q->where(function($qq){ $qq->where('stream','both')->orWhere('stream','school')->orWhere('stream','madarsa'); });
            }
        })->orderBy('test_date')->get();

        $marks = Mark::with('test')
            ->where('student_id', $student->id)
            ->get()
            ->map(function (Mark $m) {
                $total = (int) $m->test->total_marks;
                $obt   = (int) $m->obtained;

                return [
                    'type'     => $m->test->type,
                    'subject'  => $m->test->subject,
                    'date'     => $m->test->test_date,
                    'obtained' => $obt,
                    'total'    => $total,
                    'percent'  => $total > 0 ? round(($obt / $total) * 100, 1) : 0,
                ];
            });

        $alerts = Alert::where('class_no', $student->class_no)
            ->where(function ($q) use ($student) {
                $q->where('section', $student->section)->orWhere('section', '');
            })
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $payments = $student->payments()->orderByDesc('paid_on')->orderByDesc('id')->get();

        $messages = Message::forStudent($student)->orderByDesc('id')->limit(10)->get();

        $examFees = ExamFee::with('event')->where('student_id', $student->id)->orderByDesc('id')->get();

        $books = Book::query()
            ->when($stream === 'school', function($q) use ($sCls){
                $q->where(function($qq){ $qq->where('stream','school')->orWhere('stream','both'); });
                $q->where(function($qq) use ($sCls){ $qq->whereNull('class_level')->orWhere('class_level', $sCls); });
            })
            ->when($stream === 'madarsa', function($q) use ($sSec){
                $q->where(function($qq){ $qq->where('stream','madarsa')->orWhere('stream','both'); });
                $q->where(function($qq) use ($sSec){ $qq->whereNull('madarsa_section')->orWhere('madarsa_section', $sSec); });
            })
            ->when($stream === 'both', function($q){
                $q->where(function($qq){ $qq->where('stream','both')->orWhere('stream','school')->orWhere('stream','madarsa'); });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $classInfo = SchoolClass::where('class_no', $student->class_no)
            ->where('section', $student->section)
            ->first()
            ?? SchoolClass::where('class_no', $student->class_no)->first();

        return view('portal.show', [
            'student'  => $student,
            'att'      => [
                'present' => $present,
                'total'   => $total,
                'percent' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                'week'    => $week,
            ],
            'fee' => [
                'monthly'   => $monthly,
                'paid'      => $paid,
                'remaining' => $rem,
                'status'    => $feeStatus,
                'month'     => $feeRow ? date('F Y', strtotime($feeRow->month . '-01')) : date('F Y'),
                'pct'       => $feePct,
            ],
            'payments' => $payments,
            'tests'    => $tests,
            'marks'    => $marks,
            'alerts'   => $alerts,
            'messages' => $messages,
            'examFees' => $examFees,
            'books'    => $books,
            'classInfo'=> $classInfo,
            'syncStamp'=> $this->syncStamp($student),
        ]);
    }

    /** Sirf ID card ka clean printable page (poora portal nahi). */
    public function idCard(Request $request)
    {
        $student = Student::find($request->session()->get('student_id'));
        if (!$student) {
            $request->session()->flush();

            return redirect()->route('login');
        }

        return view('portal.id-card', ['student' => $student]);
    }

    public function sync(Request $request): JsonResponse
    {
        $student = Student::find($request->session()->get('student_id'));
        if (!$student) {
            return response()->json(['ok' => false, 'error' => 'Login required'], 401);
        }

        return response()->json([
            'ok'      => true,
            'stamp'   => $this->syncStamp($student),
            'message' => 'Naya update aa gaya — page refresh ho raha hai.',
        ]);
    }

    private function syncStamp(Student $student): string
    {
        $stream = $student->stream ?? 'school';
        $sCls = $student->class_level ?? $student->class_no;
        $sSec = strtoupper($student->madarsa_section ?? $student->section ?? 'A');

        $parts = [
            Test::count(),
            Mark::where('student_id', $student->id)->count(),
            Alert::max('id'),
            Fee::where('student_id', $student->id)->max('paid_amount'),
            Payment::where('student_id', $student->id)->max('id'),
            Attendance::where('student_id', $student->id)->max('id'),
            Message::max('id'),
            Book::count(),
            \App\Models\ExamFee::where('student_id', $student->id)->max('id'),
        ];

        return md5(implode('|', $parts));
    }
}
