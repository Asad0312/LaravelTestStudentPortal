@extends('layouts.admin')

@section('page_title', 'Settings')
@section('page_sub', 'School info, fees, notifications')

@section('content')
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card card-soft p-3">
      <h6 class="fw-bold mb-2">General Settings</h6>
      <form method="post" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="mb-2"><label class="muted" style="font-size:12px;">School Name</label><input class="form-ctrl" name="school_name" value="{{ $settings['school_name'] ?? 'IRAA' }}"></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Tagline</label><input class="form-ctrl" name="tagline" value="{{ $settings['tagline'] ?? 'Iqra Riffat ull Atfal Academy' }}"></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Fee Due Day</label><input class="form-ctrl" type="number" name="fee_due_day" value="{{ $settings['fee_due_day'] ?? 15 }}"></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Default Late Fine</label><input class="form-ctrl" type="number" name="default_late_fine" value="{{ $settings['default_late_fine'] ?? 0 }}"></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Attendance Notify Mode</label>
          <select class="form-ctrl" name="notify_attendance_mode">
            <option value="absent_only" {{ ($settings['notify_attendance_mode']??'absent_only')==='absent_only'?'selected':'' }}>Absent only</option>
            <option value="all" {{ ($settings['notify_attendance_mode']??'')==='all'?'selected':'' }}>All</option>
            <option value="off" {{ ($settings['notify_attendance_mode']??'')==='off'?'selected':'' }}>Off</option>
          </select>
        </div>
        <button class="btn-primary-grad">Save Settings</button>
      </form>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-soft p-3">
      <h6 class="fw-bold mb-2">Recent Notification Logs</h6>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead><tr><th>To</th><th>Type</th><th>Channel</th><th>Status</th><th>Time</th></tr></thead>
          <tbody>
            @forelse($logs as $l)
              <tr>
                <td>{{ $l->to_phone }}</td>
                <td>{{ $l->type }}</td>
                <td>{{ $l->channel }}</td>
                <td>{{ $l->status }}</td>
                <td>{{ $l->created_at }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center muted">No logs yet</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="muted" style="font-size:12px;">Default WhatsApp driver: log (set WHATSAPP_DRIVER=cloud in .env for Meta Cloud API).</div>
    </div>
  </div>
</div>
@endsection
