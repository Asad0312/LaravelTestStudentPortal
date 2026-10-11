@extends('layouts.admin')

@section('page_title', 'Admissions')
@section('page_sub', 'Admin yahan se admission form bhare — submit karte hi student ban jata hai')

@section('content')
<div class="card card-soft p-3 mb-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
  <span class="muted" style="font-size:13px;">
    <i class="fa-solid fa-circle-info text-primary"></i>
    Admission form bharein — submit karte hi student register ho jata hai aur <b>Student ID + password</b> ban jata hai (wo student ko dein). Email alert bhi chala jayega.
  </span>
  <button class="btn-primary-grad" style="padding:9px 16px;font-size:13px;" data-bs-toggle="modal" data-bs-target="#addAdmissionModal">
    <i class="fa-solid fa-plus me-1"></i> New Admission
  </button>
</div>

<div class="card card-soft p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>App ID</th><th>Student</th><th>Class</th><th>Phone</th><th>Status</th><th class="text-end">Action</th></tr></thead>
      <tbody>
        @forelse($rows as $a)
          <tr>
            <td><b>{{ $a->app_id }}</b><div class="muted" style="font-size:11.5px;">{{ $a->created_at?->format('d M Y') }}</div></td>
            <td><b style="font-size:13.5px;">{{ $a->name }}</b><div class="muted" style="font-size:11.5px;">S/o {{ $a->father_name }}</div></td>
            <td>Class {{ $a->class_no }}-{{ $a->section }}</td>
            <td>{{ $a->phone }}</td>
            <td><span class="badge {{ $a->status === 'Approved' ? 'bg-soft-success' : ($a->status === 'Rejected' ? 'bg-soft-danger' : 'bg-soft-warning') }}">{{ $a->status }}</span></td>
            <td class="text-end">
              @if($a->status === 'Pending')
                <form method="post" action="{{ route('admin.admissions.approve', $a->id) }}" class="d-inline">
                  @csrf
                  <button class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i> Approve</button>
                </form>
                <form method="post" action="{{ route('admin.admissions.reject', $a->id) }}" class="d-inline">
                  @csrf
                  <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i></button>
                </form>
              @else
                <span class="muted" style="font-size:12px;">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center muted py-4">Koi admission nahi hua abhi — "New Admission" se form bharein.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ===== NEW ADMISSION ===== --}}
<div class="modal fade" id="addAdmissionModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.admissions.store') }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">New Admission Form</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Student Name *</label><input class="form-ctrl" name="name" required /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Father Name *</label><input class="form-ctrl" name="father_name" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Phone *</label><input class="form-ctrl" name="phone" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Email</label><input class="form-ctrl" type="email" name="email" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Class *</label>
            <select class="form-ctrl" name="class_no">@for($i = 1; $i <= 12; $i++)<option value="{{ $i }}" {{ $i === 6 ? 'selected' : '' }}>Class {{ $i }}</option>@endfor</select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Section</label>
            <select class="form-ctrl" name="section">@foreach(['A','B','C','D'] as $sec)<option>{{ $sec }}</option>@endforeach</select>
          </div>
          <div class="col-md-8"><label class="muted" style="font-size:12px;">Address</label><input class="form-ctrl" name="address" /></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;">Submit Admission</button>
      </div>
    </form>
  </div>
</div>
@endsection
