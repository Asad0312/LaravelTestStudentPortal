<?php

namespace App\Services;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Monthly fee generation + carry-forward of unpaid balances.
 *
 * Rule:
 *  - Every month a fee row is created for each active student from students.monthly_fee.
 *  - Any unpaid balance from older months is added to the new month's total and the
 *    old row is marked as "carried out" so the same money is never counted twice.
 */
class FeeCarryForward
{
    /** Normalise a month to YYYY-MM (defaults to the current month). */
    public function month(?string $month = null): string
    {
        if ($month === null || $month === '') {
            return now()->format('Y-m');
        }

        if (! preg_match('/^\d{4}-\d{2}/', $month)) {
            throw new InvalidArgumentException('Month must be in YYYY-MM format.');
        }

        return substr($month, 0, 7);
    }

    /** Student's configured monthly fee, falling back to the class default. */
    public function baseFee(Student $student): int
    {
        $fee = $student->monthly_fee;

        if ($fee === null || $fee === '') {
            return Student::defaultFeeForClass((int) ($student->class_level ?? $student->class_no));
        }

        return max(0, (int) round((float) $fee));
    }

    /**
     * Create fee rows for every active student for the given month.
     *
     * @return array{month: string, created: int, carried: int, skipped: int}
     */
    public function generate(?string $month = null): array
    {
        $month = $this->month($month);

        $created = 0;
        $carried = 0;
        $skipped = 0;

        Student::where('status', 'Active')
            ->orderBy('id')
            ->chunkById(200, function ($students) use ($month, &$created, &$carried, &$skipped) {
                foreach ($students as $student) {
                    try {
                        $fee = $this->generateForStudent($student, $month);
                    } catch (UniqueConstraintViolationException) {
                        // Another request created this row at the same time.
                        $fee = null;
                    }

                    if ($fee) {
                        $created++;
                        $carried += (int) $fee->carried_amount;
                    } else {
                        $skipped++;
                    }
                }
            });

        return [
            'month'   => $month,
            'created' => $created,
            'carried' => $carried,
            'skipped' => $skipped,
        ];
    }

    /**
     * Create the fee row for one student. Returns null when it already exists,
     * when the student was not admitted yet, or when there is nothing to bill.
     */
    public function generateForStudent(Student $student, ?string $month = null): ?Fee
    {
        $month = $this->month($month);
        $monthStart = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();

        if ($student->admission_date && $student->admission_date->copy()->startOfMonth()->gt($monthStart)) {
            return null;
        }

        if (Fee::where('student_id', $student->id)->where('month', $month)->exists()) {
            return null;
        }

        $base = $this->baseFee($student);

        $prior = Fee::where('student_id', $student->id)
            ->where('month', '<', $month)
            ->orderBy('month')
            ->get();

        $carry = 0;
        $oldest = null;

        foreach ($prior as $row) {
            $outstanding = max(0, (int) $row->monthly_amount - (int) $row->paid_amount - (int) $row->carried_out);
            if ($outstanding > 0) {
                $carry += $outstanding;
                $oldest ??= $row->month;
            }
        }

        if ($base + $carry <= 0) {
            return null;
        }

        return DB::transaction(function () use ($student, $month, $base, $carry, $oldest, $prior) {
            // Mark older balances as moved so they are not counted again next month.
            if ($carry > 0) {
                foreach ($prior as $row) {
                    $outstanding = max(0, (int) $row->monthly_amount - (int) $row->paid_amount - (int) $row->carried_out);
                    if ($outstanding > 0) {
                        $row->carried_out = (int) $row->carried_out + $outstanding;
                        $row->save();
                    }
                }
            }

            return Fee::create([
                'student_id'     => $student->id,
                'month'          => $month,
                'monthly_amount' => $base + $carry,
                'carried_amount' => $carry,
                'carried_from'   => $carry > 0 ? $oldest : null,
                'carried_out'    => 0,
                'paid_amount'    => 0,
            ]);
        });
    }

    /** Keep monthly_amount in sync with the student's current fee + carried balance. */
    public function syncBase(Fee $fee, Student $student): Fee
    {
        $expected = $this->baseFee($student) + (int) $fee->carried_amount;
        $expected = max($expected, (int) $fee->paid_amount + (int) $fee->carried_out);

        if ((int) $fee->monthly_amount !== $expected) {
            $fee->monthly_amount = $expected;
            $fee->save();
        }

        return $fee;
    }

    /**
     * Record a payment. The amount is capped at what is actually payable for the
     * selected month; balances that were already carried to the next month are
     * not collected twice.
     *
     * @return array{fee: ?Fee, payment: ?Payment, applied: int, message: string}
     */
    public function recordPayment(Student $student, string $month, int $amount, string $method, ?int $base = null): array
    {
        $month = $this->month($month);

        return DB::transaction(function () use ($student, $month, $amount, $method, $base) {
            try {
                $this->generateForStudent($student, $month);
            } catch (UniqueConstraintViolationException) {
                // Row was created concurrently; just fetch it below.
            }

            $fee = Fee::where('student_id', $student->id)->where('month', $month)->first();

            if (! $fee) {
                $fee = Fee::create([
                    'student_id'     => $student->id,
                    'month'          => $month,
                    'monthly_amount' => $base ?? $this->baseFee($student),
                    'carried_amount' => 0,
                    'carried_from'   => null,
                    'carried_out'    => 0,
                    'paid_amount'    => 0,
                ]);
            }

            $carried = (int) $fee->carried_amount;
            $newBase = $base ?? $this->baseFee($student);
            $fee->monthly_amount = $newBase + $carried;
            $fee->save();

            $monthly = (int) $fee->monthly_amount;
            $paid = (int) $fee->paid_amount;
            $carriedOut = (int) $fee->carried_out;
            $payable = max(0, $monthly - $paid - $carriedOut);

            if ($payable <= 0) {
                $next = Fee::where('student_id', $student->id)
                    ->where('month', '>', $month)
                    ->orderBy('month')
                    ->first();

                $message = 'Is mahine ki fee already clear hai.';
                if ($carriedOut > 0 && $next) {
                    $message = 'Is mahine ka baqi balance ' . Carbon::parse($next->month . '-01')->format('F Y')
                        . ' ki fee me carry ho chuka hai — wahan payment karein.';
                }

                return ['fee' => $fee, 'payment' => null, 'applied' => 0, 'message' => $message];
            }

            $applied = min(max(0, $amount), $payable);

            if ($applied <= 0) {
                return ['fee' => $fee, 'payment' => null, 'applied' => 0, 'message' => 'Payment amount valid nahi hai.'];
            }

            $fee->paid_amount = $paid + $applied;
            $fee->save();

            $payment = Payment::create([
                'student_id' => $student->id,
                'month'      => $month,
                'amount'     => $applied,
                'paid_on'    => now()->toDateString(),
                'method'     => $method,
            ]);

            $message = 'Payment saved: Rs. ' . number_format($applied) . ' — ' . Carbon::parse($month . '-01')->format('F Y');
            if ($applied < $amount) {
                $message .= ' (Rs. ' . number_format($amount - $applied) . ' extra tha, sirf payable amount liya gaya).';
            }

            return ['fee' => $fee, 'payment' => $payment, 'applied' => $applied, 'message' => $message];
        });
    }
}
