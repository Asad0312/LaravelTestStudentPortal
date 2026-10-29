@extends('layouts.admin')

@section('page_title', 'Test Plan')
@section('page_sub', 'School / Madarsa / School+Madarsa ke hisab se test upload karo')

@section('content')
<div class="card card-soft p-3 mb-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
  <div class="muted" style="font-size:13px;">
    <i class="fa-solid fa-circle-info text-primary"></i>
    Ab stream select kar ke alag alag test upload kar saktay ho.
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
      <thead><tr><th>Code</th><th>Type</th><th>Subject</th><th>Audience</th><th>Date & Time</th><th>Marks</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        @forelse($tests as $t)
          @php $st = $t->stream ?? 'school'; @endphp
          <tr>
            <td><b>{{ $t->test_code }}</b></td>
            <td><span class="obj-badge {{ $t->type === 'Weekly' ? 'week' : 'month' }}">{{ $t->type }}</span></td>
            <td>{{ $t->subject }}</td>
            <td>
              {{ ucfirst($st) }}
              @if($st !== 'madarsa') | Class {{ $t->class_level ?? $t->class_no }}@endif
              @if($st !== 'school') | Sec {{ $t->madarsa_section ?? $t->section }}@endif
            </td>
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
          <tr><td colspan="7" class="text-center muted py-4">Koi test upload nahi hua</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ===== ADD TEST ===== --}}
<div class="modal fade" id="addTestModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.tests.store') }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Upload Test</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Type *</label>
            <select class="form-ctrl" name="type"><option>Weekly</option><option>Monthly</option></select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Audience Type *</label>
            <select class="form-ctrl" name="stream" id="tStream"><option value="school">School</option><option value="madarsa">Madarsa</option><option value="both">School + Madarsa</option></select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Subject *</label><input class="form-ctrl" name="subject" required placeholder="Mathematics" /></div>
          <div class="col-md-4" id="tClass"><label class="muted" style="font-size:12px;">Class *</label>
            <select class="form-ctrl" name="class_level">@for($i=1;$i<=8;$i++)<option value="{{ $i }}">Class {{ $i }}</option>@endfor</select>
          </div>
          <div class="col-md-4" id="tMadarsa" style="display:none;"><label class="muted" style="font-size:12px;">Madarsa Section *</label>
            <select class="form-ctrl" name="madarsa_section">@foreach(['A','B','C','D'] as $s)<option>{{ $s }}</option>@endforeach</select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Total Marks *</label><input class="form-ctrl" type="number" name="total_marks" value="50" min="1" required /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Test Date *</label><input class="form-ctrl" type="date" name="test_date" min="{{ now()->toDateString() }}" required /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Time</label><input class="form-ctrl" name="test_time" value="10:00 AM" /></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;">Upload & Send Alert</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== EDIT TESTS ===== --}}
@foreach($tests as $t)
@php $ts = $t->stream ?? 'school'; @endphp
<div class="modal fade" id="editTest{{ $t->id }}" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.tests.update', $t->id) }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Edit — {{ $t->test_code }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Type *</label>
            <select class="form-ctrl" name="type"><option {{ $t->type === 'Weekly' ? 'selected' : '' }}>Weekly</option><option {{ $t->type === 'Monthly' ? 'selected' : '' }}>Monthly</option></select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Audience Type *</label>
            <select class="form-ctrl" name="stream" id="teStream{{ $t->id }}"><option value="school" {{ $ts==='school'?'selected':'' }}>School</option><option value="madarsa" {{ $ts==='madarsa'?'selected':'' }}>Madarsa</option><option value="both" {{ $ts==='both'?'selected':'' }}>School + Madarsa</option></select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Subject *</label><input class="form-ctrl" name="subject" value="{{ $t->subject }}" required /></div>
          <div class="col-md-4 teClass{{ $t->id }}" style="display:{{ $ts==='madarsa'?'none':'' }};"><label class="muted" style="font-size:12px;">Class *</label>
            <select class="form-ctrl" name="class_level">@for($i=1;$i<=8;$i++)<option value="{{ $i }}" {{ ($t->class_level??$t->class_no)==$i?'selected':'' }}>Class {{ $i }}</option>@endfor</select>
          </div>
          <div class="col-md-4 teMad{{ $t->id }}" style="display:{{ $ts==='school'?'none':'' }};"><label class="muted" style="font-size:12px;">Madarsa Section *</label>
            <select class="form-ctrl" name="madarsa_section">@foreach(['A','B','C','D'] as $s)<option {{ ($t->madarsa_section??$t->section)===$s?'selected':'' }}>{{ $s }}</option>@endforeach</select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Total Marks *</label><input class="form-ctrl" type="number" name="total_marks" value="{{ $t->total_marks }}" min="1" required /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Test Date *</label><input class="form-ctrl" type="date" name="test_date" value="{{ $t->test_date?->format('Y-m-d') }}" required /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Time</label><input class="form-ctrl" name="test_time" value="{{ $t->test_time }}" /></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;">Update & Send Alert</button>
      </div>
    </form>
  </div>
</div>
<script>
document.getElementById('teStream{{ $t->id }}')?.addEventListener('change',function(){const v=this.value;const c=document.querySelector('.teClass{{ $t->id }}');const m=document.querySelector('.teMad{{ $t->id }}');if(c)c.style.display=v==='madarsa'?'none':'';if(m)m.style.display=v==='school'?'none':'';});
</script>
@endforeach
<script>
document.getElementById('tStream')?.addEventListener('change',function(){const v=this.value;document.getElementById('tClass').style.display=v==='madarsa'?'none':'';document.getElementById('tMadarsa').style.display=v==='school'?'none':'';});
</script>
@endsection
