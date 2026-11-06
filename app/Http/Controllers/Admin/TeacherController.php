<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherPayment;
use App\Models\TeacherSalary;
use App\Support\Sql;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $currentMonth = now()->format('Y-m');

        $teachers = Teacher::orderByRaw(Sql::numericSuffix('teacher_id'))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';
                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(teacher_id) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw("LOWER(COALESCE(designation,'')) LIKE ?", [$like])
                        ->orWhereRaw("LOWER(COALESCE(phone,'')) LIKE ?", [$like]);
                });
            })
            ->get()
            ->map(function (Teacher $t) use ($currentMonth) {
                $salary = $t->salaries()->where('month', $currentMonth)->first();
                if (!$salary) {
                    $salary = TeacherSalary::firstOrCreate(
                        ['teacher_id' => $t->id, 'month' => $currentMonth],
                        ['monthly_salary' => (int) $t->monthly_salary, 'paid_amount' => 0]
                    );
                } elseif ((int) $salary->monthly_salary !== (int) $t->monthly_salary && (int) $salary->paid_amount === 0) {
                    $salary->monthly_salary = (int) $t->monthly_salary;
                    $salary->save();
                }

                $monthly = (int) $salary->monthly_salary;
                $paid = (int) $salary->paid_amount;
                $remaining = max(0, $monthly - $paid);
                $status = $remaining <= 0 ? 'Paid' : ($paid > 0 ? 'Pending' : 'Unpaid');

                return [
                    'dbId' => $t->id,
                    'teacherId' => $t->teacher_id,
                    'name' => $t->name,
                    'gender' => $t->gender,
                    'designation' => $t->designation,
                    'subject' => $t->subject,
                    'phone' => $t->whatsapp ?: $t->phone ?: '—',
                    'status' => $t->status,
                    'monthly' => $monthly,
                    'paid' => $paid,
                    'remaining' => $remaining,
                    'payStatus' => $status,
                    'month' => date('F Y', strtotime($currentMonth . '-01')),
                    'monthRaw' => $currentMonth,
                    'avatar' => $t->avatar_seed ?: $t->name,
                ];
            });

        $totalSalary = (int) $teachers->sum('monthly');
        $paidSalary = (int) $teachers->sum('paid');

        return view('admin.teachers', [
            'teachers' => Teacher::orderByRaw(Sql::numericSuffix('teacher_id'))->get(),
            'rows' => $teachers,
            'q' => $q,
            'nextId' => Teacher::nextTeacherId(),
            'totalSalary' => $totalSalary,
            'paidSalary' => $paidSalary,
            'pendingSalary' => max(0, $totalSalary - $paidSalary),
            'recent' => TeacherPayment::with('teacher')->latest('id')->limit(6)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:120'],
            'subject' => ['nullable', 'string', 'max:120'],
            'qualification' => ['nullable', 'string', 'max:190'],
            'cnic' => ['nullable', 'string', 'max:30'],
            'joining_date' => ['nullable', 'date'],
            'monthly_salary' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $phone = preg_replace('/\s+/', '', (string) ($data['phone'] ?? ''));
        Teacher::create([
            'teacher_id' => Teacher::nextTeacherId(),
            'name' => $data['name'],
            'father_name' => (string) ($data['father_name'] ?? ''),
            'gender' => $data['gender'],
            'phone' => $phone,
            'whatsapp' => $phone,
            'email' => (string) ($data['email'] ?? ''),
            'address' => (string) ($data['address'] ?? ''),
            'designation' => $data['designation'],
            'subject' => (string) ($data['subject'] ?? ''),
            'qualification' => (string) ($data['qualification'] ?? ''),
            'cnic' => (string) ($data['cnic'] ?? ''),
            'joining_date' => $data['joining_date'] ?? null,
            'monthly_salary' => (int) $data['monthly_salary'],
            'status' => $data['status'] ?? 'Active',
            'notes' => $data['notes'] ?? null,
            'avatar_seed' => $data['name'],
        ]);

        return back()->with('success', 'Teacher added: ' . $data['name']);
    }

    public function update(Request $request, int $id)
    {
        $teacher = Teacher::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:120'],
            'subject' => ['nullable', 'string', 'max:120'],
            'qualification' => ['nullable', 'string', 'max:190'],
            'cnic' => ['nullable', 'string', 'max:30'],
            'joining_date' => ['nullable', 'date'],
            'monthly_salary' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $phone = preg_replace('/\s+/', '', (string) ($data['phone'] ?? ''));
        $teacher->update([
            'name' => $data['name'],
            'father_name' => (string) ($data['father_name'] ?? ''),
            'gender' => $data['gender'],
            'phone' => $phone,
            'whatsapp' => $phone,
            'email' => (string) ($data['email'] ?? ''),
            'address' => (string) ($data['address'] ?? ''),
            'designation' => $data['designation'],
            'subject' => (string) ($data['subject'] ?? ''),
            'qualification' => (string) ($data['qualification'] ?? ''),
            'cnic' => (string) ($data['cnic'] ?? ''),
            'joining_date' => $data['joining_date'] ?? null,
            'monthly_salary' => (int) $data['monthly_salary'],
            'status' => $data['status'] ?? $teacher->status,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Teacher updated: ' . $teacher->name);
    }

    public function destroy(int $id)
    {
        $teacher = Teacher::findOrFail($id);
        $name = $teacher->name;
        $teacher->delete();
        return back()->with('success', 'Teacher deleted: ' . $name);
    }

    public function pay(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'month' => ['required', 'string', 'max:7'],
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'string', 'max:40'],
            'monthly_salary' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $teacher = Teacher::findOrFail($data['teacher_id']);
        $month = substr($data['month'], 0, 7);

        DB::transaction(function () use ($teacher, $month, $data) {
            $salary = TeacherSalary::firstOrCreate(
                ['teacher_id' => $teacher->id, 'month' => $month],
                ['monthly_salary' => (int) ($data['monthly_salary'] ?? $teacher->monthly_salary), 'paid_amount' => 0]
            );
            if (isset($data['monthly_salary'])) {
                $salary->monthly_salary = (int) $data['monthly_salary'];
            }
            $payable = max(0, (int) $salary->monthly_salary - (int) $salary->paid_amount);
            $applied = min((int) $data['amount'], $payable);
            if ($applied <= 0) {
                return;
            }
            $salary->paid_amount = (int) $salary->paid_amount + $applied;
            $salary->save();

            TeacherPayment::create([
                'teacher_id' => $teacher->id,
                'month' => $month,
                'amount' => $applied,
                'paid_on' => now()->toDateString(),
                'method' => $data['method'],
                'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with('success', 'Salary payment saved for ' . $teacher->name . ' (' . date('F Y', strtotime($month . '-01')) . ').');
    }

    public function history(int $id)
    {
        $teacher = Teacher::findOrFail($id);
        $rows = TeacherSalary::where('teacher_id', $teacher->id)->orderBy('month')->get()->map(function ($s) {
            $monthly = (int) $s->monthly_salary;
            $paid = (int) $s->paid_amount;
            $rem = max(0, $monthly - $paid);
            return [
                'month' => date('F Y', strtotime($s->month . '-01')),
                'raw' => $s->month,
                'monthly' => $monthly,
                'paid' => $paid,
                'remaining' => $rem,
                'status' => $rem <= 0 ? 'Paid' : ($paid > 0 ? 'Pending' : 'Unpaid'),
            ];
        });

        return view('admin.teachers.history', compact('teacher', 'rows'));
    }
}