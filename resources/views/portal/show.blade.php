@extends('layouts.portal')

@section('title', $student->name . ' — Student Portal')

@section('content')
@php
    $avatar   = 'https://api.dicebear.com/8.x/initials/svg?seed=' . urlencode($student->avatar_seed ?: $student->name);
    $clsLabel = method_exists($student, 'getDisplayClassAttribute') ? $student->display_class : ('Class ' . ($student->class_level ?? $student->class_no) . '-' . ($student->madarsa_section ?? $student->section));
    $today    = now()->toDateString();
    $feeBars  = $fee['status'] === 'Paid'
        ? 'var(--gradient-success)'
        : ($fee['pct'] > 0 ? 'var(--gradient-warning)' : 'var(--gradient-danger)');
    $labels = [];
    for ($i = 6; $i >= 0; $i--) {
        $labels[] = date('d M', strtotime("-{$i} day"));
    }
    $qr = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($student->student_id);
@endphp

<div class="container-fluid px-lg-4 py-4" style="max-width:1200px;">

  {{-- Hero --}}
  <div class="portal-hero mb-4 anim-fade-up">
    <div class="wave-line"><i class="fa-solid fa-graduation-cap"></i></div>
    <div class="d-flex flex-sm-row flex-column align-items-sm-center gap-3 position-relative" style="z-index:2;">
      <img class="avatar-lg" src="{{ $avatar }}" alt="" />
      <div class="flex-grow-1">
        <div class="fs-5 fw-bold">{{ $student->name }}</div>
        <div class="small opacity-75">{{ $clsLabel }} • {{ $student->batch }}</div>
        <span class="badge mt-1" style="background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);">{{ $student->student_id }}</span>
      </div>
      <div class="row g-2 col-sm-8 col-lg-6">
        <div class="col-6">
          <div class="stat-pill text-center">
            <div class="fs-4 fw-bold">{{ $att['percent'] }}%</div>
            <div class="small opacity-75">Attendance</div>
          </div>
        </div>
        <div class="col-6">
          <div class="stat-pill text-center">
            <div class="fs-4 fw-bold">{{ $fee['status'] }}</div>
            <div class="small opacity-75">{{ $fee['month'] }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="news-ticker mb-4 anim-fade-up d1">
    <div class="ticker-track">
      🎉 Welcome to IRAA Student Portal — Iqra Riffat ull Atfal Academy &nbsp;•&nbsp; Admin jab bhi test ya marks upload kare ga yahan foran aa jayega &nbsp;•&nbsp; Fee pending ho to jald pay karein
    </div>
  </div>

  @if($messages->count())
  <div class="card card-soft p-3 p-md-4 mb-4 anim-fade-up d1">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="section-title mb-0"><i class="fa-solid fa-bullhorn me-2 text-danger"></i>Admin Messages</h5>
      <span class="badge bg-soft-info" style="font-size:11.5px;">{{ $messages->count() }}</span>
    </div>
    @foreach($messages as $msg)
      <div class="p-3 rounded-3 mb-2 {{ $msg->priority === 'Important' ? 'msg-important' : 'msg-normal' }}">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <div>
            @if($msg->title)
              <b style="font-size:14px;">{{ $msg->title }}</b>
              @if($msg->priority === 'Important')<span class="obj-badge month ms-1">Important</span>@endif
              <br />
            @endif
            <span style="font-size:13.5px;">{!! nl2br(e($msg->body)) !!}</span>
          </div>
          <span class="muted text-nowrap" style="font-size:11px;">{{ $msg->created_at?->format('d M Y') }}</span>
        </div>
        <div class="muted mt-1" style="font-size:11px;"><i class="fa-solid fa-user-shield me-1"></i>Admin • {{ $msg->targetLabel() }}</div>
      </div>
    @endforeach
  </div>
  @endif

  <div class="row g-3">
    <div class="col-lg-4 anim-fade-up d2">
      <div class="card card-soft p-4 text-center h-100">
        <img class="avatar-lg mx-auto" src="{{ $avatar }}" alt="" />
        <h5 class="mt-3 mb-0 fw-bold">{{ $student->name }}</h5>
        <span class="badge bg-soft-primary mt-1">{{ $student->student_id }}</span>
        <p class="muted mt-2 mb-3" style="font-size:13px;">{{ $clsLabel }} • {{ $student->batch }}</p>

        <div class="card p-3" style="background:#f8fafc;">
          <div class="muted" style="font-size:11.5px;">My QR Code — class mein scan kariye</div>
          <div class="d-flex justify-content-center my-2">
            <div style="width:140px;height:140px;background:#fff;border-radius:14px;display:grid;place-items:center;padding:8px;box-shadow:var(--shadow-sm);">
              <img src="{{ $qr }}" style="width:100%;height:100%;" alt="QR Code" />
            </div>
          </div>
          <a class="btn-grad d-block" style="padding:8px;font-size:12.5px;text-decoration:none;" href="{{ route('portal.id-card') }}" target="_blank">
            <i class="fa-solid fa-id-card me-1"></i> Download / Print ID Card
          </a>
        </div>

        <div class="text-start mt-3">
          <h6 class="fw-bold mb-2"><i class="fa-solid fa-user me-1 text-primary"></i>Profile Details</h6>
          <div class="d-flex flex-column gap-2" style="font-size:13.5px;">
            <div class="d-flex justify-content-between border-bottom pb-1" style="border-color:#f1f5f9!important;"><span class="muted">Father Name</span><b>{{ $student->father_name ?: '—' }}</b></div>
            <div class="d-flex justify-content-between border-bottom pb-1" style="border-color:#f1f5f9!important;"><span class="muted">Phone</span><b>{{ $student->phone ?: '—' }}</b></div>
            <div class="d-flex justify-content-between border-bottom pb-1" style="border-color:#f1f5f9!important;"><span class="muted">Email</span><b style="font-size:12.5px;">{{ $student->email ?: '—' }}</b></div>
            <div class="d-flex justify-content-between border-bottom pb-1" style="border-color:#f1f5f9!important;"><span class="muted">Address</span><b style="font-size:12.5px;text-align:right;">{{ $student->address ?: '—' }}</b></div>
            <div class="d-flex justify-content-between border-bottom pb-1" style="border-color:#f1f5f9!important;"><span class="muted">Admission</span><b>{{ $student->admission_date?->format('j F Y') }}</b></div>
            <div class="d-flex justify-content-between"><span class="muted">Batch</span><b>{{ $student->batch }}</b></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="row g-3">
        <div class="col-sm-6 anim-fade-up d2">
          <div class="card card-soft p-3 d-flex flex-row align-items-center gap-3 h-100">
            <div class="stat-icon bg-grad-success" style="width:48px;height:48px;font-size:18px;"><i class="fa-solid fa-chart-simple"></i></div>
            <div>
              <div class="stat-value" style="font-size:22px;">{{ $att['percent'] }}%</div>
              <div class="stat-label">Attendance ({{ $att['present'] }}/{{ $att['total'] }})</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 anim-fade-up d3">
          <div class="card card-soft p-3 d-flex flex-row align-items-center gap-3 h-100">
            <div class="stat-icon" style="width:48px;height:48px;font-size:18px;background:var(--success-light);color:#047857;"><i class="fa-solid fa-money-check-dollar"></i></div>
            <div>
              <div class="stat-value" style="font-size:22px;color:{{ $fee['status'] === 'Paid' ? '#047857' : ($fee['status'] === 'Pending' ? '#b45309' : '#be123c') }};">{{ $fee['status'] }}</div>
              <div class="stat-label">{{ $fee['month'] }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 anim-fade-up d3">
          <div class="card card-soft p-3 d-flex flex-row align-items-center gap-3 h-100">
            <div class="stat-icon bg-grad-info" style="width:48px;height:48px;font-size:18px;"><i class="fa-solid fa-book-open"></i></div>
            <div>
              <div class="stat-value" style="font-size:18px;">{{ $clsLabel }}</div>
              <div class="stat-label">Class</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 anim-fade-up d4">
          <div class="card card-soft p-3 d-flex flex-row align-items-center gap-3 h-100">
            <div class="stat-icon" style="width:48px;height:48px;font-size:18px;background:var(--warning-light);color:#b45309;"><i class="fa-solid fa-percent"></i></div>
            <div>
              <div class="stat-value" style="font-size:22px;">{{ $fee['pct'] }}%</div>
              <div class="stat-label">Fee Collection</div>
            </div>
          </div>
        </div>

        <div class="col-12 anim-fade-up d4">
          <div class="card card-soft p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="section-title">My Attendance History</h5>
              <span class="badge bg-soft-info" style="font-size:12px;">Last 7 days</span>
            </div>
            <div class="chart-box" style="height:190px;"><canvas id="attLine"></canvas></div>
          </div>
        </div>

        <div class="col-md-6 anim-fade-up">
          <div class="card card-soft p-3 h-100">
            <h5 class="section-title mb-3">Class Information</h5>
            <div class="d-flex flex-column gap-2">
              <div class="d-flex justify-content-between p-2 rounded-3" style="background:#f8fafc;"><span class="muted" style="font-size:13px;">Class</span><b>{{ $clsLabel }}</b></div>
              <div class="d-flex justify-content-between p-2 rounded-3" style="background:#f8fafc;"><span class="muted" style="font-size:13px;">Class Teacher</span><b>{{ $classInfo?->teacher_name ?: '—' }}</b></div>
              <div class="d-flex justify-content-between p-2 rounded-3" style="background:#f8fafc;"><span class="muted" style="font-size:13px;">Duration</span><b>Academic Year {{ date('Y') }}</b></div>
            </div>
          </div>
        </div>

        <div class="col-md-6 anim-fade-up d2">
          <div class="card card-soft p-3 h-100">
            <h5 class="section-title mb-3">Payment History</h5>
            <div class="timeline">
              @if($payments->isEmpty())
                <div class="muted" style="font-size:13px;">Abhi koi payment nahi hui.</div>
              @else
                @foreach($payments as $p)
                  <div class="timeline-item success">
                    <b style="font-size:13.5px;">{{ date('F Y', strtotime($p->month . '-01')) }} — {{ number_format($p->amount) }}</b>
                    <div class="muted" style="font-size:12.5px;">Rs. {{ number_format($p->amount) }} • {{ $p->paid_on?->format('d M Y') }} • {{ $p->method }}</div>
                  </div>
                @endforeach
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Fee detail --}}
  <div class="card card-soft p-4 mt-3 anim-fade-up d2">
    <h5 class="section-title mb-3"><i class="fa-solid fa-money-bill-wave me-2 text-success"></i>Fee Status — {{ $fee['month'] }}</h5>
    <div class="row g-3 text-center">
      <div class="col-4"><div class="rounded-3 p-3" style="background:#f8fafc;"><div class="muted" style="font-size:11.5px;">Total Payable</div><b class="fs-5">Rs. {{ number_format($fee['monthly']) }}</b>@if($fee['carried'] > 0)<div class="muted" style="font-size:10.5px;">Base {{ number_format($fee['base']) }} + carry {{ number_format($fee['carried']) }}</div>@endif</div></div>
      <div class="col-4"><div class="rounded-3 p-3 bg-soft-success"><div class="muted" style="font-size:11.5px;">Paid</div><b class="fs-5 text-success">Rs. {{ number_format($fee['paid']) }}</b></div></div>
      <div class="col-4"><div class="rounded-3 p-3" style="background:{{ $fee['status'] === 'Paid' ? 'var(--success-light)' : 'var(--warning-light)' }};"><div class="muted" style="font-size:11.5px;">Remaining</div><b class="fs-5" style="color:{{ $fee['status'] === 'Paid' ? '#047857' : '#b45309' }}">Rs. {{ number_format($fee['remaining']) }}</b></div></div>
    </div>
    @if($fee['carried'] > 0)
      <div class="alert alert-warning mt-3 mb-0 py-2 px-3" style="font-size:12.5px;">
        <i class="fa-solid fa-arrow-turn-up"></i>
        Pichle mahine ka baqi balance <b>Rs. {{ number_format($fee['carried']) }}</b> is mahine ki fee me add kar diya gaya hai{{ $fee['carried_from'] ? ' (' . $fee['carried_from'] . ' se)' : '' }}.
      </div>
    @endif
    @if($fee['carried_out'] > 0)
      <div class="alert alert-info mt-3 mb-0 py-2 px-3" style="font-size:12.5px;">
        <i class="fa-solid fa-arrow-turn-down"></i>
        Is mahine ka <b>Rs. {{ number_format($fee['carried_out']) }}</b> balance {{ $fee['carried_to'] }} ki fee me carry ho gaya hai.
      </div>
    @endif
    <div class="mt-3">
      <div class="d-flex justify-content-between mb-1"><span class="muted" style="font-size:12.5px;">Paid collection progress</span><b>{{ $fee['pct'] }}% paid</b></div>
      <div class="progress-soft"><div class="progress-bar" style="width:{{ $fee['pct'] }}%;background:{{ $feeBars }};"></div></div>
    </div>
  </div>

  {{-- Books / Study Material --}}
  <div class="card card-soft p-4 mt-3 anim-fade-up">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
      <h5 class="section-title mb-0"><i class="fa-solid fa-book me-2 text-primary"></i>Books / Study Material</h5>
      <span class="badge bg-soft-primary" style="font-size:11px;">{{ isset($books) ? $books->count() : 0 }} Book(s)</span>
    </div>
    @if(!isset($books) || $books->isEmpty())
      <div class="muted small py-2"><i class="fa-solid fa-inbox me-1"></i>Abhi koi book upload nahi hua.</div>
    @else
      <div class="row g-2">
        @foreach($books as $book)
          <div class="col-md-6">
            <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f8fafc;border:1px solid #eef2f7;">
              <div class="flex-shrink-0" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#0f766e,#22d3ee);color:#fff;display:grid;place-items:center;"><i class="fa-solid fa-book-open"></i></div>
              <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:13.5px;">{{ $book->title }}</div>
                <div class="muted" style="font-size:11.5px;">
                  {{ ucfirst($book->stream) }}
                  @if($book->stream !== 'madarsa' && $book->class_level) | Class {{ $book->class_level }}@endif
                  @if($book->stream !== 'school' && $book->madarsa_section) | Sec {{ $book->madarsa_section }}@endif
                </div>
              </div>
              @if(!empty($book->file_path))
                <a class="btn btn-sm btn-outline-primary" href="{{ asset($book->file_path) }}" target="_blank"><i class="fa-solid fa-download"></i></a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Exam Fees --}}
  @if(isset($examFees) && $examFees->count())
  <div class="card card-soft p-4 mt-3 anim-fade-up d2">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
      <h5 class="section-title mb-0"><i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>Examination Fees</h5>
      <span class="badge bg-soft-info" style="font-size:11px;">{{ $examFees->count() }} Exam(s)</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Exam</th><th>Month</th><th>Amount</th><th>Status</th><th>Paid On</th></tr></thead>
        <tbody>
          @foreach($examFees as $ef)
            <tr>
              <td><b>{{ $ef->event->title ?? 'Exam' }}</b></td>
              <td>{{ $ef->event && $ef->event->exam_month ? $ef->event->exam_month->format('M Y') : '—' }}</td>
              <td>Rs. {{ number_format($ef->amount) }}</td>
              <td><span class="badge {{ $ef->status === 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ ucfirst($ef->status) }}</span></td>
              <td>{{ $ef->paid_on ? $ef->paid_on->format('d-M-Y') : '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  {{-- Test alerts --}}
  <div class="card card-soft p-4 mt-3 anim-fade-up d2">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
      <h5 class="section-title mb-0"><i class="fa-solid fa-bell me-2 text-warning"></i>Test Alerts</h5>
      <span class="badge bg-soft-warning" style="font-size:11px;">Naye alerts yahan aate hain</span>
    </div>
    <p class="muted small mb-3" style="line-height:1.6;">Admin jab bhi koi test ya marks upload karta hai, tumhe yahan alert aa jata hai.</p>
    @if($alerts->isEmpty())
      <div class="muted small py-2"><i class="fa-solid fa-inbox me-1"></i>Abhi koi alert nahi.</div>
    @else
      @foreach($alerts as $a)
        <div class="d-flex align-items-start gap-3" style="padding:11px 0;border-bottom:1px solid #f1f5f9;">
          <div class="flex-shrink-0" style="width:36px;height:36px;border-radius:10px;background:var(--warning-light);color:#b45309;display:grid;place-items:center;"><i class="fa-solid fa-bell"></i></div>
          <div><b style="font-size:13.5px;">{{ $a->msg }}</b>
          <div class="muted" style="font-size:11.5px;">Published: {{ $a->created_at?->format('d M Y') }}</div></div>
        </div>
      @endforeach
    @endif
  </div>

  {{-- Test plan --}}
  <div class="card card-soft p-4 mt-3 anim-fade-up d3">
    <h5 class="section-title mb-3"><i class="fa-solid fa-list-check me-2 text-primary"></i>Test Plan (Weekly &amp; Monthly)</h5>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Type</th><th>Subject</th><th>Date</th><th>Time</th><th>Total Marks</th><th class="text-end">Status</th></tr></thead>
        <tbody>
          @if($tests->isEmpty())
            <tr><td colspan="6" class="text-center muted py-3">Is class ka koi test schedule nahi hua</td></tr>
          @else
            @foreach($tests as $t)
              <tr>
                <td><span class="obj-badge {{ $t->type === 'Weekly' ? 'week' : 'month' }}">{{ $t->type }}</span></td>
                <td><b>{{ $t->subject }}</b></td>
                <td>{{ $t->test_date?->format('D d M Y') }}</td>
                <td>{{ $t->test_time }}</td>
                <td>{{ $t->total_marks }}</td>
                <td class="text-end">
                  @if($t->test_date?->toDateString() <= $today)
                    <span class="badge bg-soft-info">Completed</span>
                  @else
                    <span class="badge bg-soft-warning">Upcoming</span>
                  @endif
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

  {{-- My marks --}}
  <div class="card card-soft p-4 mt-3 anim-fade-up d4">
    <h5 class="section-title mb-3"><i class="fa-solid fa-square-poll-vertical me-2 text-success"></i>My Marks / Results</h5>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Type</th><th>Subject</th><th>Date</th><th>Obtained</th><th>Total</th><th class="text-end">Result</th></tr></thead>
        <tbody>
          @if($marks->isEmpty())
            <tr><td colspan="6" class="text-center muted py-3">Abhi koi result nahi mila</td></tr>
          @else
            @foreach($marks as $m)
              @php
                $resCls = $m['percent'] >= 80 ? 'bg-soft-success' : ($m['percent'] >= 50 ? 'bg-soft-warning' : 'bg-soft-danger');
                $label  = $m['percent'] >= 80 ? 'Excellent' : ($m['percent'] >= 50 ? 'Pass' : 'Needs Work');
              @endphp
              <tr>
                <td><span class="obj-badge {{ $m['type'] === 'Weekly' ? 'week' : 'month' }}">{{ $m['type'] }}</span></td>
                <td><b>{{ $m['subject'] }}</b></td>
                <td>{{ \Carbon\Carbon::parse($m['date'])->format('D d M Y') }}</td>
                <td><b>{{ $m['obtained'] }}</b></td>
                <td>{{ $m['total'] }}</td>
                <td class="text-end"><span class="badge-soft {{ $resCls }}">{{ $m['percent'] }}% {{ $label }}</span></td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
    @if($marks->isEmpty())
      <div class="muted small">Abhi koi result announce nahi hua — admin marks enter karega to yahan aa jayega.</div>
    @endif
  </div>

  <div class="text-center mt-4">
    <p class="muted" style="font-size:12px;"><i class="fa-solid fa-circle-info me-1"></i>IRAA — Iqra Riffat ull Atfal Academy. Live database — aap ka data <b>MySQL</b> se aa raha hai. Sirf apna portal dikhta hai.</p>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  new Chart(document.getElementById('attLine'), {
    type: 'line',
    data: {
      labels: @json($labels),
      datasets: [{
        label: 'Attendance',
        data: @json(array_map('intval', $att['week'])),
        borderColor: '#0d9488', backgroundColor: 'rgba(13,148,136,.12)',
        fill: true, tension: .4, pointRadius: 4, pointBackgroundColor: '#0d9488', borderWidth: 3
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: { grid: { color: 'rgba(148,163,184,.15)' }, border: { display: false }, min: 0, max: 1.2,
             ticks: { stepSize: 1, callback: v => v === 1 ? 'Present' : v === 0 ? 'Absent' : '' } }
      }
    }
  });

  /* live sync — admin upload kare to portal apne aap refresh ho jaye */
  let stamp = @json($syncStamp);
  setInterval(async () => {
    if (document.hidden) return;
    try {
      const r = await fetch(@json(route('portal.sync')), { headers: { 'X-Requested-With': 'fetch' } });
      if (!r.ok) return;
      const d = await r.json();
      if (d.stamp && d.stamp !== stamp) {
        portalToast(d.message || 'Naya update aa gaya — page refresh ho raha hai.');
        setTimeout(() => location.reload(), 1200);
      }
    } catch (e) {}
  }, 15000);

  function portalToast(msg) {
    const t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;background:#0b1220;color:#fff;padding:12px 18px;border-radius:12px;font-size:13px;box-shadow:0 12px 30px rgba(11,18,32,.4);display:flex;gap:10px;align-items:center;max-width:320px;';
    t.innerHTML = '<i class="fa-solid fa-bell" style="color:#fbbf24;"></i><div>' + msg + '</div>';
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = '.5s'; setTimeout(() => t.remove(), 500); }, 4200);
  }
</script>
@endpush
