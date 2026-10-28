<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $settings = DB::table('settings')->pluck('value', 'key')->all();
        $logs = NotificationLog::latest('id')->limit(20)->get();
        return view('admin.settings', ['settings' => $settings, 'logs' => $logs]);
    }

    public function update(Request $request)
    {
        $keys = ['school_name', 'tagline', 'fee_due_day', 'default_late_fine', 'notify_attendance_mode'];
        foreach ($keys as $key) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $request->input($key), 'updated_at' => now()]);
        }
        return back()->with('success', 'Settings saved.');
    }
}
