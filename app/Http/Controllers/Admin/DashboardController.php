<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Alert;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Test;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $totalStudents = Student::count();
        $presentToday  = Attendance::where('date', $today)->where('status', 'Present')->count();

        $feeAgg = Fee::selectRaw('COALESCE(SUM(monthly_amount),0) as billed, COALESCE(SUM(paid_amount),0) as collected')
            ->where('month', DB::raw('(SELECT MAX(month) FROM fees)'))
            ->first();
        $billed    = (int) ($feeAgg->billed ?? 0);
        $collected = (int) ($feeAgg->collected ?? 0);

        try {
            $byClass = Student::selectRaw('COALESCE(class_level, class_no) as class_no, COUNT(*) as total')
                ->groupBy('class_no')
                ->orderBy('class_no')
                ->get();
        } catch (\Throwable $e) {
            $byClass = Student::selectRaw('class_no, COUNT(*) as total')
                ->groupBy('class_no')
                ->orderBy('class_no')
                ->get();
        }

        $recentAlerts = Alert::latest('id')->limit(6)->get();
        $recentPayments = Payment::with('student')->latest('id')->limit(5)->get();

        return view('admin.dashboard', [
            'totalStudents'   => $totalStudents,
            'presentToday'    => $presentToday,
            'absentToday'     => max(0, $totalStudents - $presentToday),
            'attRate'         => $totalStudents > 0 ? round(($presentToday / $totalStudents) * 100, 1) : 0,
            'billed'          => $billed,
            'collected'       => $collected,
            'pending'         => max(0, $billed - $collected),
            'testsCount'      => Test::count(),
            'marksCount'      => Mark::count(),
            'pendingAdmits'   => Admission::where('status', 'Pending')->count(),
            'byClass'         => $byClass,
            'recentAlerts'    => $recentAlerts,
            'recentPayments'  => $recentPayments,
        ]);
    }
}
