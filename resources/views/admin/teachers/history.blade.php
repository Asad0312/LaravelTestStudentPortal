@extends('layouts.admin')

@section('page_title', 'Teacher Salary History')
@section('page_sub', $teacher->name.' ('.$teacher->teacher_id.') • '.$teacher->designation)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <a href="{{ route('admin.teachers.index') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left me-1"></i> Back to Teachers</a>
  <div class="muted">Monthly Salary (Current): Rs. {{ number_format($teacher->monthly_salary) }}</div>
</div>

<div class="card card-soft p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Month</th><th>Salary</th><th>Paid</th><th>Remaining</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td><b>{{ $r['month'] }}</b></td>
            <td>Rs. {{ number_format($r['monthly']) }}</td>
            <td class="text-success">Rs. {{ number_format($r['paid']) }}</td>
            <td class="{{ $r['remaining'] > 0 ? 'text-danger' : '' }}">Rs. {{ number_format($r['remaining']) }}</td>
            <td><span class="badge {{ $r['status'] === 'Paid' ? 'bg-soft-success' : ($r['status'] === 'Pending' ? 'bg-soft-warning' : 'bg-soft-danger') }}">{{ $r['status'] }}</span></td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center muted py-4">Koi salary record nahi mila.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
