<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Test;
use App\Services\UploadNotifier;
use App\Support\Sql;
use Illuminate\Http\Request;

class MarkController extends Controller
{
    public function index(Request $request)
    {
        $tests = Test::orderBy('test_date', 'desc')->get();
        $code  = (string) $request->query('test', $tests->first()?->test_code ?? '');

        $test    = $code !== '' ? Test::where('test_code', $code)->first() : null;
        $rows    = collect();
        $saved   = [];

        if ($test) {
            $rows = Student::where('class_no', $test->class_no)
                ->where('section', $test->section)
                ->orderByRaw(Sql::numericSuffix('student_id'))
                ->get();

            $saved = Mark::where('test_id', $test->id)
                ->pluck('obtained', 'student_id')
                ->all();
        }

        return view('admin.marks', [
            'tests' => $tests,
            'test'  => $test,
            'rows'  => $rows,
            'saved' => $saved,
        ]);
    }

    public function save(Request $request, UploadNotifier $notifier)
    {
        $data = $request->validate([
            'test_code' => ['required', 'string'],
            'entries'   => ['nullable', 'array'],
        ]);

        $test = Test::where('test_code', $data['test_code'])->first();
        if (!$test) {
            return back()->with('error', 'Test nahi mila.');
        }

        $entries = $data['entries'] ?? [];
        $saved   = 0;

        foreach ($entries as $studentId => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $obtained = (int) $value;
            if ($obtained < 0 || $obtained > (int) $test->total_marks) {
                continue;
            }

            Mark::updateOrCreate(
                ['test_id' => $test->id, 'student_id' => (int) $studentId],
                ['obtained' => $obtained]
            );
            $saved++;
        }

        if ($saved > 0) {
            $msg = "{$saved} students ke marks save ho gaye — {$test->subject} ({$test->test_code}), "
                . "Class {$test->class_no}-{$test->section}. Students ke portal par foran updated.";

            $notifier->notify('Marks uploaded — ' . $test->subject, $msg, (int) $test->class_no, $test->section);

            return back()->with('success', $msg);
        }

        return back()->with('error', 'Koi marks enter nahi kiye gaye.');
    }
}
