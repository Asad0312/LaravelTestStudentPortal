<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Support\Sql;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    public function index()
    {
        $students = Student::orderByRaw(Sql::numericSuffix('student_id'))
            ->get()
            ->map(function (Student $s) {
                $fee = $s->fees()->orderByDesc('month')->first();

                $monthly = (int) ($fee->monthly_amount ?? 0);
                $paid    = (int) ($fee->paid_amount ?? 0);
                $rem     = max(0, $monthly - $paid);

                return [
                    'dbId'      => $s->id,
                    'studentId' => $s->student_id,
                    'name'      => $s->name,
                    'cls'       => 'Class ' . $s->class_no . '-' . $s->section,
                    'avatar'    => $s->avatar_seed,
                    'monthly'   => $monthly,
                    'paid'      => $paid,
                    'remaining' => $rem,
                    'status'    => $rem <= 0 ? 'Paid' : ($paid > 0 ? 'Pending' : 'Unpaid'),
                    'month'     => $fee ? date('F Y', strtotime($fee->month . '-01')) : '—',
                ];
            });

        $billed    = (int) $students->sum('monthly');
        $collected = (int) $students->sum('paid');

        return view('admin.fees', [
            'rows'     => $students,
            'billed'   => $billed,
            'collected'=> $collected,
            'pending'  => max(0, $billed - $collected),
            'recent'   => Payment::with('student')->latest('id')->limit(6)->get(),
        ]);
    }

    public function addPayment(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'month'      => ['required', 'string', 'max:7'],
            'amount'     => ['required', 'integer', 'min:1'],
            'method'     => ['required', 'string', 'max:40'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $month   = substr($data['month'], 0, 7);
        $amount  = (int) $data['amount'];

        DB::transaction(function () use ($student, $month, $amount, $data) {
            $fee = Fee::firstOrCreate(
                ['student_id' => $student->id, 'month' => $month],
                ['monthly_amount' => (int) ($student->monthly_fee ?? Student::defaultFeeForClass((int) ($student->class_level ?? $student->class_no))), 'paid_amount' => 0]
            );

            $fee->update([
                'paid_amount' => min((int) $fee->monthly_amount, (int) $fee->paid_amount + $amount),
            ]);

            Payment::create([
                'student_id' => $student->id,
                'month'      => $month,
                'amount'     => $amount,
                'paid_on'    => now()->toDateString(),
                'method'     => $data['method'],
            ]);
        });

        return back()->with('success', 'Payment saved: ' . $student->name . ' — Rs. ' . number_format($amount));
    }
}
