@extends('layouts.admin')

@section('page_title', 'Fee History')
@section('page_sub', $student->name.' ('.$student->student_id.') • '.(method_exists($student,'getDisplayClassAttribute') ? $student->display_class : ('Class '.($student->class_level ?? $student->class_no).'-'.($student->madarsa_section ?? $student->section))))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <a href="{{ route('admin.fees.index') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left me-1"></i> Back to Fee Management</a>
  <div class="muted">Monthly Fee (Current): Rs. {{ number_format($student->monthly_fee ?? 0) }}</div>
</div>

<div class="card card-soft p-3 mb-3">
  <div class="muted" style="font-size:12.5px;">
    <i class="fa-solid fa-circle-info text-primary"></i>
    Agar kisi mahine ki fee complete nahi hui to uska baqi balance next month ki fee me add hota hai.
    History me <b>Carried In</b> (pichle mahine se aaya) aur <b>Carried Out</b> (aglay mahine me gaya) dono dikhte hain.
  </div>
</div>

<div class="card card-soft p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Month</th><th>Base</th><th>Monthly Total</th><th>Paid</th><th>Remaining</th><th>Status</th><th class="text-end">Slip</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td>
              <b>{{ $r['month'] }}</b>
              @if($r['carriedIn'] > 0)
                <div class="badge bg-soft-warning mt-1" style="font-size:10.5px;text-align:left;">
                  <i class="fa-solid fa-arrow-turn-up"></i> Rs. {{ number_format($r['carriedIn']) }} carried in{{ $r['carriedFrom'] ? ' (' . $r['carriedFrom'] . ' se)' : '' }}
                </div>
              @endif
              @if($r['carriedOut'] > 0)
                <div class="badge bg-soft-info mt-1" style="font-size:10.5px;text-align:left;">
                  <i class="fa-solid fa-arrow-turn-down"></i> Rs. {{ number_format($r['carriedOut']) }} carried to {{ $r['carriedTo'] }}
                </div>
              @endif
            </td>
            <td>Rs. {{ number_format($r['base']) }}</td>
            <td>Rs. {{ number_format($r['monthly']) }}</td>
            <td class="text-success">Rs. {{ number_format($r['paid']) }}</td>
            <td class="{{ $r['remaining'] > 0 ? 'text-danger' : '' }}">Rs. {{ number_format($r['remaining']) }}</td>
            <td>
              <span class="badge {{ $r['status'] === 'Paid' ? 'bg-soft-success' : ($r['status'] === 'Pending' ? 'bg-soft-warning' : ($r['status'] === 'Carried' ? 'bg-soft-info' : 'bg-soft-danger')) }}">{{ $r['status'] }}</span>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('admin.fees.slip', [$student->id, $r['raw']]) }}"><i class="fa-solid fa-print"></i> Print Slip</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center muted py-4">Koi fee record nahi mila.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
