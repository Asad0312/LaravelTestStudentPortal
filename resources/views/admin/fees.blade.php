@extends('layouts.admin')

@section('page_title', 'Fee Management')
@section('page_sub', 'Billed Rs. ' . number_format($billed) . ' • Collected Rs. ' . number_format($collected))

@section('content')
<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card card-soft p-3 text-center"><div class="stat-value">Rs. {{ number_format($billed) }}</div><div class="stat-label">Total Billed</div></div>
  </div>
  <div class="col-md-4">
    <div class="card card-soft p-3 text-center"><div class="stat-value text-success">Rs. {{ number_format($collected) }}</div><div class="stat-label">Collected</div></div>
  </div>
  <div class="col-md-4">
    <div class="card card-soft p-3 text-center"><div class="stat-value text-danger">Rs. {{ number_format($pending) }}</div><div class="stat-label">Pending</div></div>
  </div>
</div>

<div class="card card-soft p-3 mb-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
  <span class="muted" style="font-size:13px;"><i class="fa-solid fa-circle-info text-primary"></i> Payment add karein — student ke portal par foran update ho jata hai.</span>
  <a class="btn-grad" style="padding:9px 14px;font-size:13px;" href="{{ url('admin/export/fees') }}"><i class="fa-solid fa-file-csv me-1"></i> CSV</a>
</div>

<div class="card card-soft p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Student</th><th>Class</th><th>Monthly</th><th>Paid</th><th>Remaining</th><th>Status</th><th class="text-end">Action</th></tr></thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td><b style="font-size:13.5px;">{{ $r['name'] }}</b><div class="muted" style="font-size:11.5px;">{{ $r['studentId'] }} • {{ $r['month'] }}</div></td>
            <td>{{ $r['cls'] }}</td>
            <td>Rs. {{ number_format($r['monthly']) }}</td>
            <td class="text-success">Rs. {{ number_format($r['paid']) }}</td>
            <td class="{{ $r['remaining'] > 0 ? 'text-danger' : '' }}">Rs. {{ number_format($r['remaining']) }}</td>
            <td><span class="badge {{ $r['status'] === 'Paid' ? 'bg-soft-success' : ($r['status'] === 'Pending' ? 'bg-soft-warning' : 'bg-soft-danger') }}">{{ $r['status'] }}</span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('admin.fees.slip', [$r['dbId'], date('Y-m')]) }}"><i class="fa-solid fa-print"></i> Print Slip</a>
              <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $r['dbId'] }}"><i class="fa-solid fa-plus"></i> Payment</button>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center muted py-4">Koi student nahi — pehle students add karein.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@foreach($rows as $r)
<div class="modal fade" id="payModal{{ $r['dbId'] }}" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <form class="modal-content" method="post" action="{{ route('admin.fees.payment') }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Payment — {{ $r['name'] }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="student_id" value="{{ $r['dbId'] }}" />
        <div class="mb-2"><label class="muted" style="font-size:12px;">Month *</label><input class="form-ctrl" type="month" name="month" value="{{ date('Y-m') }}" required /></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Amount *</label><input class="form-ctrl" type="number" name="amount" min="1" value="{{ max(1, $r['remaining']) }}" required /></div>
        <div><label class="muted" style="font-size:12px;">Method *</label>
          <select class="form-ctrl" name="method"><option>Cash</option><option>JazzCash</option><option>Easypaisa</option><option>Bank Transfer</option></select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:8px 16px;">Save</button>
      </div>
    </form>
  </div>
</div>
@endforeach
@endsection
