@extends('layouts.admin')

@section('page_title', 'Exams & Exam Fee')
@section('page_sub', 'Announce exams per audience and collect exam fees')

@section('content')
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card card-soft p-3">
      <h6 class="fw-bold mb-2">Announce New Exam</h6>
      <form method="post" action="{{ route('admin.exams.store') }}">
        @csrf
        <div class="mb-2"><label class="muted" style="font-size:12px;">Title *</label><input class="form-ctrl" name="title" required placeholder="Mid Term Exam"></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Exam Month *</label><input class="form-ctrl" type="month" name="exam_month" value="{{ date('Y-m') }}" required></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Audience Type *</label>
          <select class="form-ctrl" name="stream" id="exStream">
            <option value="school">School</option>
            <option value="madarsa">Madarsa</option>
            <option value="both">School + Madarsa</option>
          </select>
        </div>
        <div class="mb-2" id="exClass"><label class="muted" style="font-size:12px;">Class (leave empty for all)</label>
          <select class="form-ctrl" name="class_level"><option value="">Sab</option>@for($i=1;$i<=8;$i++)<option value="{{ $i }}">Class {{ $i }}</option>@endfor</select>
        </div>
        <div class="mb-2" id="exSec" style="display:none;"><label class="muted" style="font-size:12px;">Madarsa Section (leave empty for all)</label>
          <select class="form-ctrl" name="madarsa_section"><option value="">Sab</option>@foreach(['A','B','C','D'] as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach</select>
        </div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Exam Fee (Rs.) *</label><input class="form-ctrl" type="number" name="exam_fee" value="500" required></div>
        <button class="btn-primary-grad">Announce & Generate Fees</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card card-soft p-3">
      <h6 class="fw-bold mb-2">Exam Events</h6>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead><tr><th>Title</th><th>Month</th><th>Audience</th><th>Fee</th><th class="text-end">Collect</th></tr></thead>
          <tbody>
            @forelse($events as $e)
              <tr>
                <td><b>{{ $e->title }}</b></td>
                <td>{{ $e->exam_month->format('M Y') }}</td>
                <td>{{ ucfirst($e->stream) }} @if($e->class_level) | Class {{ $e->class_level }}@endif @if($e->madarsa_section) | Sec {{ $e->madarsa_section }}@endif</td>
                <td>Rs. {{ number_format($e->exam_fee) }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#list{{ $e->id }}"><i class="fa-solid fa-list"></i> Fee List</button>
                  <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#col{{ $e->id }}"><i class="fa-solid fa-coins"></i> Collect</button>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center muted">Koi exam nahi announce hua.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@foreach($events as $e)
<div class="modal fade" id="list{{ $e->id }}" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h6 class="modal-title fw-bold">{{ $e->title }} — Exam Fee List</h6>
          <div class="muted" style="font-size:12px;">{{ $e->exam_month->format('F Y') }} • {{ ucfirst($e->stream) }} @if($e->class_level) | Class {{ $e->class_level }}@endif @if($e->madarsa_section) | Sec {{ $e->madarsa_section }}@endif • Fee: Rs. {{ number_format($e->exam_fee) }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Student ID</th><th>Student</th><th>Class</th><th>Amount</th><th>Status</th><th>Paid On</th><th class="text-end">Action</th></tr></thead>
            <tbody>
              @forelse($e->fees->sortBy(function($f){ return $f->student ? $f->student->student_id : ''; }) as $fee)
                <tr>
                  <td><span class="badge bg-light text-dark">{{ $fee->student->student_id ?? '—' }}</span></td>
                  <td><b>{{ $fee->student->name ?? '—' }}</b><div class="muted" style="font-size:11px;">{{ $fee->student->father_name ?? '' }}</div></td>
                  <td>{{ $fee->student->display_class ?? ($fee->student ? ('Class '.($fee->student->class_level??$fee->student->class_no).'-'.($fee->student->madarsa_section??$fee->student->section)) : '—') }}</td>
                  <td>Rs. {{ number_format($fee->amount) }}</td>
                  <td><span class="badge {{ $fee->status === 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ ucfirst($fee->status) }}</span></td>
                  <td>{{ $fee->paid_on ? $fee->paid_on->format('d-M-Y') : '—' }}</td>
                  <td class="text-end">
                    @if($fee->status !== 'paid')
                      <form method="post" action="{{ route('admin.exams.markPaid') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="exam_fee_id" value="{{ $fee->id }}">
                        <input type="hidden" name="amount" value="{{ $fee->amount }}">
                        <button class="btn btn-sm btn-success"><i class="fa-solid fa-check me-1"></i>Mark Paid</button>
                      </form>
                    @else
                      <span class="muted" style="font-size:12px;"><i class="fa-solid fa-circle-check text-success"></i> Paid</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="7" class="text-center muted py-3">Koi fee record nahi mila.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

<div class="modal fade" id="col{{ $e->id }}" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <form class="modal-content" method="post" action="{{ route('admin.exams.collect') }}">
      @csrf
      <input type="hidden" name="exam_event_id" value="{{ $e->id }}">
      <div class="modal-header"><h6 class="modal-title fw-bold">Collect Exam Fee</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="muted" style="font-size:12px;">Student ID *</label><input class="form-ctrl" name="student_id" placeholder="STU-1001" required></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Amount *</label><input class="form-ctrl" type="number" name="amount" value="{{ $e->exam_fee }}" required></div>
      </div>
      <div class="modal-footer"><button class="btn-primary-grad" style="padding:8px 14px;">Collect</button></div>
    </form>
  </div>
</div>
@endforeach

<script>
document.getElementById('exStream')?.addEventListener('change', function(){
  const v=this.value;
  document.getElementById('exClass').style.display = v==='madarsa'?'none':'';
  document.getElementById('exSec').style.display = v==='school'?'none':'';
});
</script>
@endsection
