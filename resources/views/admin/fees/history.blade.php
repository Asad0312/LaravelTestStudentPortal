@extends('layouts.admin')

@section('page_title', 'Fee History')
@section('page_sub', $student->name.' ('.$student->student_id.') • '.method_exists($student,'getDisplayClassAttribute')?$student->display_class:('Class '.($student->class_level??$student->class_no).'-'.($student->madarsa_section??$student->section)))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <a href="{{ route('admin.fees.index') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left me-1"></i> Back to Fee Management</a>
  <div class="muted">Monthly Fee (Current): Rs. {{ number_format($student->monthly_fee ?? 0) }}</div>
</div>

<div class="card card-soft p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Month</th><th>Monthly Amount</th><th>Paid</th><th>Remaining</th><th>Status</th><th class="text-end">Slip</th></tr></thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td><b>{{ $r['month'] }}</b></td>
            <td>Rs. {{ number_format($r['monthly']) }}</td>
            <td class="text-success">Rs. {{ number_format($r['paid']) }}</td>
            <td class="{{ $r['remaining'] > 0 ? 'text-danger' : '' }}">Rs. {{ number_format($r['remaining']) }}</td>
            <td><span class="badge {{ $r['status'] === 'Paid' ? 'bg-soft-success' : ($r['status'] === 'Pending' ? 'bg-soft-warning' : 'bg-soft-danger') }}">{{ $r['status'] }}</span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('admin.fees.slip', [$student->id, $r['raw']]) }}"><i class="fa-solid fa-print"></i> Print Slip</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center muted py-4">Koi fee record nahi mila.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
