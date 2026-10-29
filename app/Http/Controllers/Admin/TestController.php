<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Mark;
use App\Models\Test;
use App\Services\UploadNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        return view('admin.tests', [
            'tests' => Test::orderBy('test_date', 'desc')->orderBy('test_time')->get(),
        ]);
    }

    public function store(Request $request, UploadNotifier $notifier)
    {
        $data = $this->validated($request);

        $test = Test::create($data + ['test_code' => Test::nextTestCode()]);

        $msg = $this->alertMessage($test, 'New');
        Alert::create([
            'class_no' => $test->class_no,
            'section'  => $test->section,
            'msg'      => $msg,
        ]);

        $notifier->notify(
            $test->type . ' Test uploaded — ' . $test->subject,
            $msg,
            (int) ($test->class_no ?? 0),
            $test->section ?? 'A'
        );

        return back()->with('success', "{$test->type} — {$test->subject} upload ho gaya ({$test->test_code}). Alert students ke portal par aur email par bhej diya gaya.");
    }

    public function update(Request $request, int $id, UploadNotifier $notifier)
    {
        $test = Test::find($id);
        if (!$test) {
            return back()->with('error', 'Test nahi mila.');
        }

        $test->update($this->validated($request, $test));

        $msg = $this->alertMessage($test, 'Test Updated');
        Alert::create([
            'class_no' => $test->class_no,
            'section'  => $test->section,
            'msg'      => $msg,
        ]);

        $notifier->notify(
            'Test updated — ' . $test->subject,
            $msg,
            (int) $test->class_no,
            $test->section
        );

        return back()->with('success', $test->subject . ' update ho gaya. Alert bhej diya gaya.');
    }

    public function destroy(int $id)
    {
        $test = Test::find($id);
        if (!$test) {
            return back()->with('error', 'Test nahi mila.');
        }

        Mark::where('test_id', $test->id)->delete();
        $test->delete();

        return back()->with('success', 'Test plan se remove ho gaya.');
    }

    private function validated(Request $request, ?Test $existing = null): array
    {
        $dateRules = ['required', 'date'];
        // Naya test sirf future date par. Purana test agar date na badli ho to chalti rehne dein.
        $unchanged = $existing && $request->input('test_date') === $existing->test_date?->format('Y-m-d');
        if (!$unchanged) {
            $dateRules[] = 'after_or_equal:today';
        }

        $data = $request->validate([
            'type'            => ['required', 'in:Weekly,Monthly'],
            'stream'          => ['required', 'in:school,madarsa,both'],
            'class_level'     => ['nullable', 'integer', 'between:1,8'],
            'madarsa_section' => ['nullable', 'string', 'max:1'],
            'class_no'        => ['nullable', 'integer', 'between:1,12'],
            'section'         => ['nullable', 'string', 'max:8'],
            'subject'         => ['required', 'string', 'max:120'],
            'test_date'       => $dateRules,
            'test_time'       => ['nullable', 'string', 'max:20'],
            'total_marks'     => ['required', 'integer', 'min:1', 'max:1000'],
        ], [
            'test_date.after_or_equal' => 'Test ki date guzar chuki hai — sirf aaj ya future ki date daalein.',
        ]);

        $stream = $data['stream'];
        $cls    = (int) ($data['class_level'] ?? $data['class_no'] ?? 6);
        $msec   = strtoupper($data['madarsa_section'] ?? $data['section'] ?? 'A');

        $data['class_no'] = $cls;
        $data['section']  = $msec;

        if ($stream === 'madarsa') {
            $data['class_level'] = null;
        } else {
            $data['class_level'] = $cls;
        }

        if ($stream === 'school') {
            $data['madarsa_section'] = null;
        } else {
            $data['madarsa_section'] = $msec;
        }

        $data['test_time'] = $data['test_time'] ?: '10:00 AM';

        return $data;
    }

    private function alertMessage(Test $test, string $prefix): string
    {
        $date = Carbon::parse($test->test_date);
        $stream = $test->stream ?? 'school';
        $aud = match($stream){
            'madarsa' => 'Madarsa',
            'both' => 'School + Madarsa',
            default => 'School'
        };

        if ($stream === 'madarsa') {
            return sprintf('%s — %s (%s | Sec %s) on %s at %s. Total marks: %d', $prefix, $test->subject, $aud, $test->madarsa_section ?? $test->section, $date->format('D d M Y'), $test->test_time, (int) $test->total_marks);
        }

        if ($stream === 'both') {
            $cls = $test->class_level ?? $test->class_no;
            $msec = $test->madarsa_section ?? $test->section;
            return sprintf('%s — %s (%s | Class %s | Madarsa Sec %s) on %s at %s. Total marks: %d', $prefix, $test->subject, $aud, $cls, $msec, $date->format('D d M Y'), $test->test_time, (int) $test->total_marks);
        }

        $cls = $test->class_level ?? $test->class_no;
        return sprintf('%s — %s (%s | Class %d-%s) on %s at %s. Total marks: %d', $prefix, $test->subject, $aud, (int)$cls, $test->section, $date->format('D d M Y'), $test->test_time, (int) $test->total_marks);
    }
}
