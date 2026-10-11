@extends('layouts.admin')

@section('page_title', 'Marks')
@section('page_sub', 'Marks enter karein — save hote hi students ke portal par aa jayenge + email alert jayega')

@section('content')
<div class="card card-soft p-3 mb-3">
  <form method="get" action="{{ url('admin/marks') }}" class="d-flex flex-wrap gap-2 align-items-center">
    <label class="muted" style="font-size:13px;">Test choose karein:</label>
    <select class="form-ctrl" name="test" style="min-width:320px;">
      @forelse($tests as $t)
        <option value="{{ $t->test_code }}" {{ $test && $test->test_code === $t->test_code ? 'selected' : '' }}>
          {{ $t->test_code }} — {{ $t->subject }} (Class {{ $t->class_no }}-{{ $t->section }}, {{ $t->total_marks }} marks)
        </option>
      @empty
        <option value="">Pehle test upload karein</option>
      @endforelse
    </select>
    <button class="btn-grad" style="padding:9px 18px;font-size:13px;" {{ $tests->isEmpty() ? 'disabled' : '' }}>Load</button>
    <a class="btn-outline-grad" style="padding:9px 14px;font-size:13px;border:1px solid #cbd5e1;border-radius:10px;text-decoration:none;color:var(--primary-dark);" href="{{ url('admin/tests') }}">+ Upload New Test</a>
  </form>
</div>

@if($test)
<form method="post" action="{{ route('admin.marks.save') }}">
  @csrf
  <input type="hidden" name="test_code" value="{{ $test->test_code }}" />
  <div class="card card-soft p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="section-title mb-0">
        {{ $test->type }} — {{ $test->subject }}
        <span class="muted" style="font-size:12px;font-weight:400;">Class {{ $test->class_no }}-{{ $test->section }} • {{ $test->test_date?->format('d M Y') }}</span>
      </h5>
      <span class="badge bg-soft-warning">Out of {{ $test->total_marks }}</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Student</th><th>ID</th><th style="width:180px;">Obtained Marks</th></tr></thead>
        <tbody>
          @forelse($rows as $s)
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img class="avatar" style="width:30px;height:30px;" src="https://api.dicebear.com/8.x/initials/svg?seed={{ urlencode($s->avatar_seed ?: $s->name) }}" alt="" />
                  <b style="font-size:13.5px;">{{ $s->name }}</b>
                </div>
              </td>
              <td><span class="badge bg-soft-primary">{{ $s->student_id }}</span></td>
              <td>
                <input class="form-ctrl" type="number" name="entries[{{ $s->id }}]"
                       min="0" max="{{ $test->total_marks }}"
                       value="{{ $saved[$s->id] ?? '' }}" placeholder="—" />
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center muted py-3">Is class-section me koi student nahi hai.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($rows->isNotEmpty())
      <div class="text-end">
        <button type="submit" class="btn-primary-grad" style="padding:10px 22px;">
          <i class="fa-solid fa-cloud-arrow-up me-1"></i> Save Marks &amp; Send Alert
        </button>
      </div>
    @endif
  </div>
</form>
@elseif($tests->isEmpty())
  <div class="card card-soft p-4 text-center muted">
    Pehle <a href="{{ url('admin/tests') }}">Test Plan</a> me test upload karein, phir yahan marks enter karein.
  </div>
@endif
@endsection
