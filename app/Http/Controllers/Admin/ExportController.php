<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function export(string $type): StreamedResponse
    {
        $filename = $type . '-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($type) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            if ($type === 'attendance') {
                fputcsv($out, ['Date', 'Student ID', 'Name', 'Class', 'Status', 'Check-in']);
                foreach (\App\Models\Attendance::with('student')->orderByDesc('date')->get() as $a) {
                    fputcsv($out, [
                        $a->date,
                        $a->student?->student_id,
                        $a->student?->name,
                        $a->student ? 'Class ' . $a->student->class_no . '-' . $a->student->section : '',
                        $a->status,
                        $a->check_in_at ?? '',
                    ]);
                }
            } elseif ($type === 'fees') {
                fputcsv($out, ['Student ID', 'Name', 'Class', 'Monthly (Base)', 'Carried', 'Total Payable', 'Paid', 'Remaining', 'Status', 'Month']);
                foreach (Student::with('fees')->get() as $s) {
                    $fee       = $s->fees->sortByDesc('month')->first();
                    $monthly   = (int) ($fee->monthly_amount ?? 0);
                    $carried   = (int) ($fee->carried_amount ?? 0);
                    $carriedOut= (int) ($fee->carried_out ?? 0);
                    $paid      = (int) ($fee->paid_amount ?? 0);
                    $rem       = max(0, $monthly - $paid - $carriedOut);
                    fputcsv($out, [
                        $s->student_id,
                        $s->name,
                        'Class ' . $s->class_no . '-' . $s->section,
                        max(0, $monthly - $carried),
                        $carried,
                        $monthly,
                        $paid,
                        $rem,
                        $rem <= 0 ? ($carriedOut > 0 ? 'Carried' : 'Paid') : ($paid > 0 ? 'Pending' : 'Unpaid'),
                        $fee->month ?? '',
                    ]);
                }
            } else {
                fputcsv($out, ['Student ID', 'Name', 'Father Name', 'Phone', 'Email', 'Address', 'Class', 'Status', 'Admission']);
                foreach (Student::orderBy('student_id')->get() as $s) {
                    fputcsv($out, [
                        $s->student_id,
                        $s->name,
                        $s->father_name,
                        $s->phone,
                        $s->email,
                        $s->address,
                        'Class ' . $s->class_no . '-' . $s->section,
                        $s->status,
                        optional($s->admission_date)->format('Y-m-d'),
                    ]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
