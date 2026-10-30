<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Services\FeeCarryForward;
use App\Support\Sql;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function __construct(private FeeCarryForward $fees)
    {
    }

    public function index(Request $request)
    {
        $currentMonth = now()->format('Y-m');

        // Make sure this month's rows exist (also carries unpaid balances forward).
        $missing = Student::where('status', 'Active')
            ->whereDoesntHave('fees', fn ($q) => $q->where('month', $currentMonth))
            ->exists();

        if ($missing) {
            $this->fees->generate($currentMonth);
        }

        $q = trim((string) $request->query('q', ''));

        $students = Student::orderByRaw(Sql::numericSuffix('student_id'))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';

                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(student_id) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw("LOWER(COALESCE(father_name, '')) LIKE ?", [$like])
                        ->orWhereRaw("LOWER(COALESCE(phone, '')) LIKE ?", [$like])
                        ->orWhereRaw("LOWER(COALESCE(whatsapp, '')) LIKE ?", [$like]);
                });
            })
            ->get()
            ->map(function (Student $s) use ($currentMonth) {
                $fee = $s->fees()->where('month', $currentMonth)->first();
                if (! $fee) {
                    $fee = $s->fees()->orderByDesc('month')->first();
                }
                if ($fee) {
                    $fee = $this->fees->syncBase($fee, $s);
                }

                $monthly    = (int) ($fee->monthly_amount ?? $this->fees->baseFee($s));
                $paid       = (int) ($fee->paid_amount ?? 0);
                $carried    = (int) ($fee->carried_amount ?? 0);
                $carriedOut = (int) ($fee->carried_out ?? 0);
                $remaining  = max(0, $monthly - $paid - $carriedOut);

                $status = $remaining <= 0
                    ? ($carriedOut > 0 ? 'Carried' : 'Paid')
                    : ($paid > 0 ? 'Pending' : 'Unpaid');

                return [
                    'dbId'        => $s->id,
                    'studentId'   => $s->student_id,
                    'name'        => $s->name,
                    'cls'         => method_exists($s, 'getDisplayClassAttribute')
                        ? $s->display_class
                        : ('Class ' . ($s->class_level ?? $s->class_no) . '-' . ($s->madarsa_section ?? $s->section)),
                    'avatar'      => $s->avatar_seed,
                    'phone'       => $s->whatsapp ?: $s->phone ?: '—',
                    'base'        => max(0, $monthly - $carried),
                    'monthly'     => $monthly,
                    'paid'        => $paid,
                    'remaining'   => $remaining,
                    'status'      => $status,
                    'carried'     => $carried,
                    'carriedFrom' => $fee && $fee->carried_from
                        ? Carbon::parse($fee->carried_from . '-01')->format('F Y')
                        : null,
                    'carriedOut'  => $carriedOut,
                    'carriedTo'   => $carriedOut > 0 && $fee
                        ? Carbon::parse($fee->month . '-01')->addMonth()->format('F Y')
                        : null,
                    'month'       => $fee
                        ? Carbon::parse($fee->month . '-01')->format('F Y')
                        : Carbon::parse($currentMonth . '-01')->format('F Y'),
                    'monthRaw'    => $fee ? $fee->month : $currentMonth,
                ];
            });

        $billed        = (int) $students->sum('monthly');
        $collected     = (int) $students->sum('paid');
        $carriedTotal  = (int) $students->sum('carried');

        return view('admin.fees', [
            'rows'         => $students,
            'billed'       => $billed,
            'collected'    => $collected,
            'pending'      => max(0, $billed - $collected),
            'carriedTotal' => $carriedTotal,
            'q'            => $q,
            'recent'       => Payment::with('student')->latest('id')->limit(6)->get(),
        ]);
    }

    /** Manually create this month's fee rows (for hosts where cron is not set up). */
    public function generate()
    {
        $result = $this->fees->generate();
        $label  = Carbon::parse($result['month'] . '-01')->format('F Y');

        $message = $label . ': ' . $result['created'] . ' fee row(s) create hui';
        if ($result['carried'] > 0) {
            $message .= ', Rs. ' . number_format($result['carried']) . ' previous balance carry forward hua';
        }

        return back()->with('success', $message . '.');
    }

    public function addPayment(Request $request)
    {
        $data = $request->validate([
            'student_id'     => ['required', 'integer', 'exists:students,id'],
            'month'          => ['required', 'string', 'max:7'],
            'amount'         => ['required', 'integer', 'min:1'],
            'method'         => ['required', 'string', 'max:40'],
            'monthly_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $base    = isset($data['monthly_amount']) && $data['monthly_amount'] !== null
            ? (int) $data['monthly_amount']
            : null;

        $result = $this->fees->recordPayment(
            $student,
            $data['month'],
            (int) $data['amount'],
            $data['method'],
            $base
        );

        if (! $result['payment']) {
            return back()->with('error', $student->name . ' — ' . $result['message']);
        }

        return back()->with('success', $student->name . ' — ' . $result['message']);
    }

    public function slip(Request $request, int $studentId, string $month)
    {
        $student = Student::findOrFail($studentId);
        $month   = $this->fees->month($month);
        $fee     = Fee::where('student_id', $student->id)->where('month', $month)->first();

        if ($fee) {
            $fee = $this->fees->syncBase($fee, $student);
        }

        $monthly    = (int) ($fee->monthly_amount ?? $this->fees->baseFee($student));
        $paid       = (int) ($fee->paid_amount ?? 0);
        $carried    = (int) ($fee->carried_amount ?? 0);
        $carriedOut = (int) ($fee->carried_out ?? 0);
        $remaining  = max(0, $monthly - $paid - $carriedOut);

        $payments = Payment::where('student_id', $student->id)
            ->where('month', $month)
            ->orderBy('id')
            ->get();

        return view('admin.fees.slip', [
            'student'    => $student,
            'month'      => $month,
            'base'       => max(0, $monthly - $carried),
            'monthly'    => $monthly,
            'carried'    => $carried,
            'carriedFrom'=> $fee && $fee->carried_from
                ? Carbon::parse($fee->carried_from . '-01')->format('F Y')
                : null,
            'carriedOut' => $carriedOut,
            'carriedTo'  => $carriedOut > 0
                ? Carbon::parse($month . '-01')->addMonth()->format('F Y')
                : null,
            'paid'       => $paid,
            'remaining'  => $remaining,
            'payments'   => $payments,
        ]);
    }

    public function history(int $studentId)
    {
        $student  = Student::findOrFail($studentId);
        $fees     = Fee::where('student_id', $student->id)->orderBy('month')->get();
        $payments = Payment::where('student_id', $student->id)
            ->orderBy('month')
            ->orderBy('id')
            ->get()
            ->groupBy('month');

        $rows = $fees->map(function (Fee $fee) use ($payments) {
            $pays       = $payments->get($fee->month, collect());
            $monthly    = (int) $fee->monthly_amount;
            $paid       = (int) $fee->paid_amount;
            $carriedIn  = (int) $fee->carried_amount;
            $carriedOut = (int) $fee->carried_out;
            $remaining  = max(0, $monthly - $paid - $carriedOut);

            $status = $remaining <= 0
                ? ($carriedOut > 0 ? 'Carried' : 'Paid')
                : ($paid > 0 ? 'Pending' : 'Unpaid');

            return [
                'month'       => Carbon::parse($fee->month . '-01')->format('F Y'),
                'raw'         => $fee->month,
                'base'        => max(0, $monthly - $carriedIn),
                'carriedIn'   => $carriedIn,
                'carriedFrom' => $fee->carried_from
                    ? Carbon::parse($fee->carried_from . '-01')->format('F Y')
                    : null,
                'carriedOut'  => $carriedOut,
                'carriedTo'   => $carriedOut > 0
                    ? Carbon::parse($fee->month . '-01')->addMonth()->format('F Y')
                    : null,
                'monthly'     => $monthly,
                'paid'        => $paid,
                'remaining'   => $remaining,
                'status'      => $status,
                'payments'    => $pays,
            ];
        });

        return view('admin.fees.history', compact('student', 'rows'));
    }
}
