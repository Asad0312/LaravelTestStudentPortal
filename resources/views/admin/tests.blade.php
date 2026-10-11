@extends('layouts.admin')

@section('page_title', 'Test Plan')
@section('page_sub', 'Admin jo bhi test upload kare — wo student portal par aa jayega + email alert jayega')

@section('content')
<div class="card card-soft p-3 mb-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
  <div class="muted" style="font-size:13px;">
    <i class="fa-solid fa-circle-info text-primary"></i>
    Har upload par: <b>us class ke students/parents + admin</b> ko email alert • WhatsApp code .env me key daalte hi on ho jayega.
  </div>
  <div class="d-flex gap-2">
    <button class="btn-primary-grad" style="padding:9px 16px;font-size:13px;" data-bs-toggle="modal" data-bs-target="#addTestModal">
      <i class="fa-solid fa-plus me-1"></i> Upload Test
    </button>
  </div>
</div>

<div class="card card-soft p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Code</th><th>Type</th><th>Subject</th><th>Class</th><th>Date &amp; Time</th><th>Marks</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        @forelse($tests as $t)
          <tr>
            <td><b>{{ $t->test_code }}</b></td>
            <td><span class="obj-badge {{ $t->type === 'Weekly' ? 'week' : 'month' }}">{{ $t->type }}</span></td>
            <td>{{ $t->subject }}</td>
            <td>Class {{ $t->class_no }}-{{ $t->section }}</td>
            <td>{{ $t->test_date?->format('D d M Y') }} • {{ $t->test_time }}</td>
            <td>{{ $t->total_marks }}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTest{{ $t->id }}"><i class="fa-solid fa-pen"></i></button>
              <form method="post" action="{{ route('admin.tests.destroy', $t->id) }}" class="d-inline" onsubmit="return confirm('Test delete karein? Marks bhi delete ho jayenge.')">
                @csrf
                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center muted py-4">Koi test upload nahi hua — "Upload Test" se pehla test banayein.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ===== ADD TEST ===== --}}
<div class="modal fade" id="addTestModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="{{ route('admin.tests.store') }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Upload Test</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-6"><label class="muted" style="font-size:12px;">Type *</label>
            <select class="form-ctrl" name="type"><option>Weekly</option><option>Monthly</option></select>
          </div>
          <div class="col-6"><label class="muted" style="font-size:12px;">Subject *</label><input class="form-ctrl" name="subject" required placeholder="Mathematics" /></div>
          <div class="col-4"><label class="muted" style="font-size:12px;">Class *</label>
            <select class="form-ctrl" name="class_no">@for($i = 1; $i <= 12; $i++)<option value="{{ $i }}" {{ $i === 6 ? 'selected' : '' }}>Class {{ $i }}</option>@endfor</select>
          </div>
          <div class="col-4"><label class="muted" style="font-size:12px;">Section *</label>
            <select class="form-ctrl" name="section">@foreach(['A','B','C','D'] as $sec)<option>{{ $sec }}</option>@endforeach</select>
          </div>
          <div class="col-4"><label class="muted" style="font-size:12px;">Total Marks *</label><input class="form-ctrl" type="number" name="total_marks" value="50" min="1" required /></div>
          <div class="col-6"><label class="muted" style="font-size:12px;">Test Date *</label><input class="form-ctrl" type="date" name="test_date" min="{{ now()->toDateString() }}" required /><div class="muted" style="font-size:11px;">Sirf aaj ya future ki date.</div></div>
          <div class="col-6"><label class="muted" style="font-size:12px;">Time</label><input class="form-ctrl" name="test_time" value="10:00 AM" /></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;">Upload &amp; Send Alert</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== EDIT TESTS ===== --}}
@foreach($tests as $t)
<div class="modal fade" id="editTest{{ $t->id }}" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="{{ route('admin.tests.update', $t->id) }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Edit — {{ $t->test_code }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-6"><label class="muted" style="font-size:12px;">Type *</label>
            <select class="form-ctrl" name="type"><option {{ $t->type === 'Weekly' ? 'selected' : '' }}>Weekly</option><option {{ $t->type === 'Monthly' ? 'selected' : '' }}>Monthly</option></select>
          </div>
          <div class="col-6"><label class="muted" style="font-size:12px;">Subject *</label><input class="form-ctrl" name="subject" value="{{ $t->subject }}" required /></div>
          <div class="col-4"><label class="muted" style="font-size:12px;">Class *</label>
            <select class="form-ctrl" name="class_no">@for($i = 1; $i <= 12; $i++)<option value="{{ $i }}" {{ $t->class_no === $i ? 'selected' : '' }}>Class {{ $i }}</option>@endfor</select>
          </div>
          <div class="col-4"><label class="muted" style="font-size:12px;">Section *</label>
            <select class="form-ctrl" name="section">@foreach(['A','B','C','D'] as $sec)<option {{ $t->section === $sec ? 'selected' : '' }}>{{ $sec }}</option>@endforeach</select>
          </div>
          <div class="col-4"><label class="muted" style="font-size:12px;">Total Marks *</label><input class="form-ctrl" type="number" name="total_marks" value="{{ $t->total_marks }}" min="1" required /></div>
          <div class="col-6"><label class="muted" style="font-size:12px;">Test Date *</label><input class="form-ctrl" type="date" name="test_date" value="{{ $t->test_date?->format('Y-m-d') }}" required /></div>
          <div class="col-6"><label class="muted" style="font-size:12px;">Time</label><input class="form-ctrl" name="test_time" value="{{ $t->test_time }}" /></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;">Update &amp; Send Alert</button>
      </div>
    </form>
  </div>
</div>
@endforeach
@endsection
