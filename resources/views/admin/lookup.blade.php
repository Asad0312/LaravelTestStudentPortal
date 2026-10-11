@extends('layouts.admin')

@section('page_title', 'Student Lookup')
@section('page_sub', 'Student ID likhein — poori detail ek jagah')

@section('content')
<div class="card card-soft p-3 mb-3">
  <form method="get" action="{{ route('admin.lookup.index') }}" class="row g-2 align-items-end">
    <div class="col-md-8">
      <label class="muted" style="font-size:12px;">Student ID / Name / Phone</label>
      <div class="field" style="margin:0;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input class="form-ctrl" name="q" value="{{ $q }}" placeholder="e.g. STU-1001" autofocus required />
      </div>
    </div>
    <div class="col-md-4 d-flex gap-2">
      <button class="btn-primary-grad flex-grow-1" style="padding:11px 18px;"><i class="fa-solid fa-magnifying-glass me-1"></i> Search</button>
      @if($q !== '')<a class="btn btn-light" style="padding:11px 16px;" href="{{ route('admin.lookup.index') }}">Clear</a>@endif
    </div>
  </form>
</div>

@if($q !== '' && !$student)
  <div class="flash flash-err"><i class="fa-solid fa-circle-exclamation"></i> "{{ $q }}" ke naam se koi student nahi mila.</div>
@endif

@if($student)
  @php
      $avatar = 'https://api.dicebear.com/8.x/initials/svg?seed=' . urlencode($student->avatar_seed ?: $student->name);
      $qr = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($student->student_id);
      $att = $data['attendance'];
  @endphp

  <div class="row g-3">
    {{-- Profile --}}
    <div class="col-lg-4">
      <div class="card card-soft p-4 text-center h-100">
        <img class="avatar-lg mx-auto" src="{{ $avatar }}" alt="" />
        <h5 class="mt-3 mb-0 fw-bold">{{ $student->name }}</h5>
        <span class="badge bg-soft-primary mt-1">{{ $student->student_id }}</span>
        <p class="muted mt-2 mb-3" style="font-size:13px;">Class {{ $student->class_no }}-{{ $student->section }} • {{ $student->batch }}</p>
        <span class="badge {{ $student->status === 'Active' ? 'bg-soft-success' : 'bg-soft-danger' }}">{{ $student->status }}</span>

        <div class="text-start mt-3" style="font-size:13px;">
          <div class="d-flex justify-content-between border-bottom py-1"><span class="muted">Father Name</span><b>{{ $student->father_name ?: '—' }}</b></div>
          <div class="d-flex justify-content-between border-bottom py-1"><span class="muted">Phone</span><b>{{ $student->phone ?: '—' }}</b></div>
          <div class="d-flex justify-content-between border-bottom py-1"><span class="muted">Email</span><b style="font-size:12px;">{{ $student->email ?: '—' }}</b></div>
          <div class="d-flex justify-content-between border-bottom py-1"><span class="muted">Address</span><b style="font-size:12px;text-align:right;">{{ $student->address ?: '—' }}</b></div>
          <div class="d-flex justify-content-between border-bottom py-1"><span class="muted">Admission</span><b>{{ $student->admission_date?->format('d M Y') }}</b></div>
          <div class="d-flex justify-content-between border-bottom py-1"><span class="muted">Class Teacher</span><b>{{ $data['classInfo']?->teacher_name ?: '—' }}</b></div>
          <div class="d-flex justify-content-between py-1"><span class="muted">Portal Password</span><b style="font-size:12px;">{{ \App\Models\Student::defaultPassword($student->name) }}</b></div>
        </div>

        <div class="d-flex justify-content-center mt-3">
          <img src="{{ $qr }}" style="width:110px;height:110px;border:1px solid #e2e8f0;border-radius:12px;padding:5px;background:#fff;" alt="QR" />
        </div>
      </div>
    </div>

    {{-- Stats + detail --}}
    <div class="col-lg-8">
      <div class="row g-3">
        <div class="col-sm-4">
          <div class="card card-soft p-3 text-center h-100">
            <div class="stat-value" style="font-size:24px;">{{ $att['percent'] }}%</div>
            <div class="stat-label">Attendance</div>
            <div class="muted" style="font-size:11.5px;">{{ $att['present'] }}/{{ $att['total'] }} present</div>
          </div>
        </div>
        @php
            $lastFee = $data['fees']->first();
            $feeMonthly = (int) ($lastFee->monthly_amount ?? 0);
            $feePaid = (int) ($lastFee->paid_amount ?? 0);
            $feeRem = max(0, $feeMonthly - $feePaid);
        @endphp
        <div class="col-sm-4">
          <div class="card card-soft p-3 text-center h-100">
            <div class="stat-value" style="font-size:20px;color:{{ $feeRem <= 0 ? '#047857' : '#b45309' }};">{{ $feeRem <= 0 ? 'Paid' : 'Rs ' . number_format($feeRem) }}</div>
            <div class="stat-label">Fee {{ $lastFee ? date('M Y', strtotime($lastFee->month . '-01')) : '' }}</div>
            <div class="muted" style="font-size:11.5px;">Paid Rs {{ number_format($feePaid) }} / {{ number_format($feeMonthly) }}</div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="card card-soft p-3 text-center h-100">
            <div class="stat-value" style="font-size:24px;">{{ $data['marks']->count() }}</div>
            <div class="stat-label">Results</div>
            <div class="muted" style="font-size:11.5px;">{{ $data['tests']->count() }} tests scheduled</div>
          </div>
        </div>

        {{-- Marks --}}
        <div class="col-12">
          <div class="card card-soft p-3">
            <h5 class="section-title mb-2">Marks / Results</h5>
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead><tr><th>Type</th><th>Subject</th><th>Date</th><th>Obtained</th><th>Total</th><th class="text-end">%</th></tr></thead>
                <tbody>
                  @forelse($data['marks'] as $m)
                    @php $pct = $m->test->total_marks > 0 ? round(($m->obtained / $m->test->total_marks) * 100) : 0; @endphp
                    <tr>
                      <td><span class="obj-badge {{ $m->test->type === 'Weekly' ? 'week' : 'month' }}">{{ $m->test->type }}</span></td>
                      <td>{{ $m->test->subject }}</td>
                      <td class="muted" style="font-size:12px;">{{ $m->test->test_date?->format('d M Y') }}</td>
                      <td><b>{{ $m->obtained }}</b></td>
                      <td>{{ $m->test->total_marks }}</td>
                      <td class="text-end"><span class="badge {{ $pct >= 50 ? 'bg-soft-success' : 'bg-soft-danger' }}">{{ $pct }}%</span></td>
                    </tr>
                  @empty
                    <tr><td colspan="6" class="text-center muted py-3">Koi result nahi.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {{-- Fees --}}
        <div class="col-md-6">
          <div class="card card-soft p-3 h-100">
            <h5 class="section-title mb-2">Fee Records</h5>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Month</th><th>Monthly</th><th>Paid</th><th class="text-end">Status</th></tr></thead>
                <tbody>
                  @forelse($data['fees'] as $f)
                    @php $paid = (int) $f->paid_amount >= (int) $f->monthly_amount; @endphp
                    <tr>
                      <td style="font-size:12.5px;">{{ date('M Y', strtotime($f->month . '-01')) }}</td>
                      <td>{{ number_format($f->monthly_amount) }}</td>
                      <td>{{ number_format($f->paid_amount) }}</td>
                      <td class="text-end"><span class="badge {{ $paid ? 'bg-soft-success' : 'bg-soft-warning' }}">{{ $paid ? 'Paid' : 'Pending' }}</span></td>
                    </tr>
                  @empty
                    <tr><td colspan="4" class="text-center muted py-3">Koi fee record nahi.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {{-- Payments --}}
        <div class="col-md-6">
          <div class="card card-soft p-3 h-100">
            <h5 class="section-title mb-2">Payments</h5>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Date</th><th>Amount</th><th>Method</th></tr></thead>
                <tbody>
                  @forelse($data['payments'] as $p)
                    <tr>
                      <td class="muted" style="font-size:12.5px;">{{ $p->paid_on?->format('d M Y') }}</td>
                      <td><b>{{ number_format($p->amount) }}</b></td>
                      <td>{{ $p->method }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="text-center muted py-3">Koi payment nahi.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {{-- Attendance recent --}}
        <div class="col-md-6">
          <div class="card card-soft p-3 h-100">
            <h5 class="section-title mb-2">Recent Attendance</h5>
            <div class="d-flex flex-wrap gap-2">
              @forelse($att['recent'] as $ar)
                <span class="badge {{ $ar->status === 'Present' ? 'bg-soft-success' : 'bg-soft-danger' }}" style="font-size:11.5px;">
                  {{ $ar->date?->format('d M') }} • {{ $ar->status }}
                </span>
              @empty
                <span class="muted" style="font-size:12.5px;">Koi attendance record nahi.</span>
              @endforelse
            </div>
          </div>
        </div>

        {{-- Messages to this student --}}
        <div class="col-md-6">
          <div class="card card-soft p-3 h-100">
            <h5 class="section-title mb-2">Messages (student ko dikhte)</h5>
            @forelse($data['messages'] as $msg)
              <div class="border-bottom pb-2 mb-2">
                @if($msg->title)<b style="font-size:13px;">{{ $msg->title }}</b><br />@endif
                <span style="font-size:12.5px;">{{ \Illuminate\Support\Str::limit($msg->body, 110) }}</span>
                <div class="muted" style="font-size:11px;">{{ $msg->created_at?->format('d M Y') }} • {{ $msg->targetLabel() }}</div>
              </div>
            @empty
              <span class="muted" style="font-size:12.5px;">Abhi koi message nahi bheja.</span>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

@if($q === '')
  <div class="card card-soft p-3">
    <h5 class="section-title mb-3">Recent Students (quick open)</h5>
    <div class="d-flex flex-wrap gap-2">
      @forelse($recent as $s)
        <a href="{{ route('admin.lookup.index', ['q' => $s->student_id]) }}" class="btn btn-sm btn-outline-primary">
          <b>{{ $s->student_id }}</b> — {{ $s->name }} ({{ $s->class_no }}-{{ $s->section }})
        </a>
      @empty
        <span class="muted" style="font-size:13px;">Abhi koi student nahi — pehle Students page se add karein.</span>
      @endforelse
    </div>
  </div>
@endif
@endsection
