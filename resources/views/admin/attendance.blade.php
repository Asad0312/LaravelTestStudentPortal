@extends('layouts.admin')

@section('page_title', 'Attendance')
@section('page_sub', 'Today — ' . date('d M Y'))

@section('content')
<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value">{{ $students->count() }}</div><div class="stat-label">Total Students</div></div>
  </div>
  <div class="col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value text-success">{{ $present }}</div><div class="stat-label">Present</div></div>
  </div>
  <div class="col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value text-danger">{{ $absent }}</div><div class="stat-label">Absent</div></div>
  </div>
  <div class="col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value">{{ $rate }}%</div><div class="stat-label">Attendance Rate</div></div>
  </div>
</div>

<div class="card card-soft p-3 mb-3">
  <form method="post" action="{{ route('admin.attendance.mark') }}" class="d-flex flex-wrap gap-2 align-items-center">
    @csrf
    <input class="form-ctrl" name="student_id" placeholder="Student ID — e.g. STU-1001" style="min-width:240px;" required />
    <input type="hidden" name="status" id="attStatus" value="Present" />
    <button class="btn-primary-grad" style="padding:9px 18px;font-size:13px;" onclick="document.getElementById('attStatus').value='Present'">
      <i class="fa-solid fa-check me-1"></i> Mark Present
    </button>
    <button class="btn btn-outline-danger" style="padding:9px 18px;font-size:13px;" onclick="document.getElementById('attStatus').value='Absent';this.form.submit();return false;">
      <i class="fa-solid fa-xmark me-1"></i> Mark Absent
    </button>
    <span class="muted" style="font-size:12px;">(QR scan ki jagah ID daal kar mark karein)</span>
  </form>
</div>

<div class="card card-soft p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Student</th><th>ID</th><th>Class</th><th>Status Today</th><th>Check-in</th></tr></thead>
      <tbody>
        @forelse($students as $s)
          @php($row = $marked->get($s->id))
          <tr>
            <td><b style="font-size:13.5px;">{{ $s->name }}</b></td>
            <td><span class="badge bg-soft-primary">{{ $s->student_id }}</span></td>
            <td>Class {{ $s->class_no }}-{{ $s->section }}</td>
            <td>
              @if($row)
                <span class="badge {{ $row->status === 'Present' ? 'bg-soft-success' : 'bg-soft-danger' }}">{{ $row->status }}</span>
              @else
                <span class="badge bg-soft-warning">Not marked</span>
              @endif
            </td>
            <td>{{ $row?->check_in_at ? date('h:i A', strtotime($row->check_in_at)) : '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center muted py-4">Koi student nahi add hua — pehle <a href="{{ url('admin/students') }}">Students</a> me add karein.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
