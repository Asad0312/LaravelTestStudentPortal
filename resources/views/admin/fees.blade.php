@extends('layouts.admin')

@section('page_title', 'Fee Management')
@section('page_sub', 'Billed Rs. ' . number_format($billed) . ' • Collected Rs. ' . number_format($collected))

@section('content')
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value">Rs. {{ number_format($billed) }}</div><div class="stat-label">Total Billed</div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value text-success">Rs. {{ number_format($collected) }}</div><div class="stat-label">Collected</div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value text-danger">Rs. {{ number_format($pending) }}</div><div class="stat-label">Pending</div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value text-warning">Rs. {{ number_format($carriedTotal) }}</div><div class="stat-label">Carried Forward</div></div>
  </div>
</div>

<div class="card card-soft p-3 mb-3">
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <form method="get" action="{{ route('admin.fees.index') }}" class="d-flex flex-wrap gap-2" style="flex:1;min-width:280px;">
      <input class="form-ctrl" type="text" name="q" value="{{ $q }}" placeholder="Student ID / name / phone — e.g. STU-1" style="flex:1;min-width:220px;" />
      <button class="btn-primary-grad" style="padding:9px 16px;font-size:13px;" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i> Filter</button>
      @if($q !== '')
        <a class="btn btn-light" style="padding:9px 14px;font-size:13px;" href="{{ route('admin.fees.index') }}">Clear</a>
      @endif
    </form>
    <div class="d-flex flex-wrap gap-2">
      <form method="post" action="{{ route('admin.fees.generate') }}" class="m-0" onsubmit="return confirm('Is mahine ki fee rows ban jayengi. Pichle mahine ka baqi balance next month ki fee me add hoga. Continue?')">
        @csrf
        <button class="btn btn-outline-success" style="padding:9px 14px;font-size:13px;" type="submit"><i class="fa-solid fa-rotate me-1"></i> Generate This Month</button>
      </form>
      <a class="btn-grad" style="padding:9px 14px;font-size:13px;" href="{{ url('admin/export/fees') }}"><i class="fa-solid fa-file-csv me-1"></i> CSV</a>
    </div>
  </div>
  <div class="muted mt-2" style="font-size:12px;">
    <i class="fa-solid fa-circle-info text-primary"></i>
    Har mahine ki 1 tareekh ko active students ki fee row automatically banti hai. Pichle mahine ka baqi balance next month ki fee me add hota hai.
    @if($q !== '')
      <span class="ms-1">Filter: <b>{{ $q }}</b> — {{ $rows->count() }} result(s).</span>
    @endif
  </div>
</div>

<div class="card card-soft p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Student</th><th>ID</th><th>Class</th><th>Contact</th><th>Monthly</th><th>Paid</th><th>Remaining</th><th>Status</th><th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td>
              <b style="font-size:13.5px;">{{ $r['name'] }}</b>
              <div class="muted" style="font-size:11.5px;">{{ $r['month'] }}</div>
              @if($r['carried'] > 0)
                <div class="badge bg-soft-warning mt-1" style="font-size:10.5px;text-align:left;">
                  <i class="fa-solid fa-arrow-turn-up"></i> Rs. {{ number_format($r['carried']) }} carried from {{ $r['carriedFrom'] }}
                </div>
              @endif
              @if($r['carriedOut'] > 0)
                <div class="badge bg-soft-info mt-1" style="font-size:10.5px;text-align:left;">
                  <i class="fa-solid fa-arrow-turn-down"></i> Rs. {{ number_format($r['carriedOut']) }} carried to {{ $r['carriedTo'] }}
                </div>
              @endif
            </td>
            <td><span class="badge bg-soft-primary">{{ $r['studentId'] }}</span></td>
            <td>{{ $r['cls'] }}</td>
            <td>{{ $r['phone'] ?? '—' }}</td>
            <td>
              Rs. {{ number_format($r['monthly']) }}
              @if($r['carried'] > 0)
                <div class="muted" style="font-size:10.5px;">Base {{ number_format($r['base']) }} + carry {{ number_format($r['carried']) }}</div>
              @endif
            </td>
            <td class="text-success">Rs. {{ number_format($r['paid']) }}</td>
            <td class="{{ $r['remaining'] > 0 ? 'text-danger' : '' }}">Rs. {{ number_format($r['remaining']) }}</td>
            <td>
              <span class="badge {{ $r['status'] === 'Paid' ? 'bg-soft-success' : ($r['status'] === 'Pending' ? 'bg-soft-warning' : ($r['status'] === 'Carried' ? 'bg-soft-info' : 'bg-soft-danger')) }}">{{ $r['status'] }}</span>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-info" href="{{ route('admin.fees.history', $r['dbId']) }}"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
              <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('admin.fees.slip', [$r['dbId'], $r['monthRaw']]) }}"><i class="fa-solid fa-print"></i> Slip</a>
              <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $r['dbId'] }}"><i class="fa-solid fa-plus"></i> Payment</button>
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="text-center muted py-4">
            @if($q !== '')
              Is ID / search se koi student nahi mila — <a href="{{ route('admin.fees.index') }}">filter clear karein</a>.
            @else
              Koi student nahi — pehle students add karein.
            @endif
          </td></tr>
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
        <div class="mb-2">
          <label class="muted" style="font-size:12px;">Student ID</label>
          <input class="form-ctrl" type="text" value="{{ $r['studentId'] }}" readonly />
        </div>
        <div class="mb-2">
          <label class="muted" style="font-size:12px;">Month *</label>
          <input class="form-ctrl" type="month" name="month" value="{{ $r['monthRaw'] }}" required />
        </div>
        <div class="mb-2">
          <label class="muted" style="font-size:12px;">Monthly Fee (base) *</label>
          <input class="form-ctrl fee-base-input" type="number" name="monthly_amount" min="0" value="{{ $r['base'] }}"
                 data-carry="{{ $r['carried'] }}" data-total="payTotal{{ $r['dbId'] }}" required />
        </div>
        @if($r['carried'] > 0)
          <div class="alert alert-warning py-2 px-3 mb-2" style="font-size:12px;">
            <i class="fa-solid fa-arrow-turn-up"></i>
            Pichle balance ka <b>Rs. {{ number_format($r['carried']) }}</b> is mahine ki fee me add ho chuka hai{{ $r['carriedFrom'] ? ' (' . $r['carriedFrom'] . ' se)' : '' }}.
          </div>
        @endif
        <div class="mb-2 d-flex justify-content-between align-items-center rounded-3 px-3 py-2" style="background:#f8fafc;">
          <span class="muted" style="font-size:12px;">Total Payable</span>
          <b>Rs. <span id="payTotal{{ $r['dbId'] }}">{{ number_format($r['monthly']) }}</span></b>
        </div>
        <div class="mb-2">
          <label class="muted" style="font-size:12px;">Amount Paid *</label>
          <input class="form-ctrl" type="number" name="amount" min="1" value="{{ max(1, $r['remaining']) }}" required />
        </div>
        <div>
          <label class="muted" style="font-size:12px;">Method *</label>
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

@push('scripts')
<script>
document.querySelectorAll('.fee-base-input').forEach(function (input) {
  input.addEventListener('input', function () {
    var carry = parseInt(this.dataset.carry || '0', 10) || 0;
    var base = parseInt(this.value || '0', 10) || 0;
    var target = document.getElementById(this.dataset.total);
    if (target) {
      target.textContent = Math.max(0, base + carry).toLocaleString();
    }
  });
});
</script>
@endpush
