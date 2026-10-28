@extends('layouts.admin')

@section('page_title', 'Dashboard')
@section('page_sub', 'IRAA overview — ' . date('l, j F Y'))

@section('content')
<div class="row g-3 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="card card-soft stat-card p-3 d-flex flex-row align-items-center gap-3">
      <div class="stat-icon bg-grad-primary"><i class="fa-solid fa-users"></i></div>
      <div>
        <div class="stat-value">{{ $totalStudents }}</div>
        <div class="stat-label">Total Students</div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card card-soft stat-card p-3 d-flex flex-row align-items-center gap-3">
      <div class="stat-icon bg-grad-success"><i class="fa-solid fa-calendar-check"></i></div>
      <div>
        <div class="stat-value">{{ $presentToday }}</div>
        <div class="stat-label">Present Today ({{ $attRate }}%)</div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card card-soft stat-card p-3 d-flex flex-row align-items-center gap-3">
      <div class="stat-icon bg-grad-info"><i class="fa-solid fa-money-bill-wave"></i></div>
      <div>
        <div class="stat-value">Rs. {{ number_format($collected) }}</div>
        <div class="stat-label">Fees Collected</div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card card-soft stat-card p-3 d-flex flex-row align-items-center gap-3">
      <div class="stat-icon" style="background:var(--warning-light);color:#b45309;"><i class="fa-solid fa-hourglass-half"></i></div>
      <div>
        <div class="stat-value">Rs. {{ number_format($pending) }}</div>
        <div class="stat-label">Pending Fees</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card card-soft p-3 text-center">
      <div class="stat-value">{{ $testsCount }}</div>
      <div class="stat-label">Tests Uploaded</div>
      <a href="{{ url('admin/tests') }}" class="btn btn-sm btn-outline-success mt-2">Upload Test</a>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-soft p-3 text-center">
      <div class="stat-value">{{ $marksCount }}</div>
      <div class="stat-label">Marks Entered</div>
      <a href="{{ url('admin/marks') }}" class="btn btn-sm btn-outline-primary mt-2">Enter Marks</a>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-soft p-3 text-center">
      <div class="stat-value">{{ $pendingAdmits }}</div>
      <div class="stat-label">Pending Admissions</div>
      <a href="{{ url('admin/admissions') }}" class="btn btn-sm btn-outline-warning mt-2">Review</a>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card card-soft p-3 h-100">
      <h5 class="section-title mb-3"><i class="fa-solid fa-bell me-2 text-warning"></i>Recent Alerts</h5>
      @forelse($recentAlerts as $a)
        <div class="d-flex align-items-start gap-3" style="padding:10px 0;border-bottom:1px solid #f1f5f9;">
          <div class="flex-shrink-0" style="width:34px;height:34px;border-radius:10px;background:var(--warning-light);color:#b45309;display:grid;place-items:center;"><i class="fa-solid fa-bell"></i></div>
          <div>
            <b style="font-size:13px;">{{ $a->msg }}</b>
            <div class="muted" style="font-size:11.5px;">Class {{ $a->class_no }}-{{ $a->section }} • {{ $a->created_at?->format('d M Y') }}</div>
          </div>
        </div>
      @empty
        <div class="muted small">Abhi koi alert nahi — students add karne par alerts yahan dikhte hain.</div>
      @endforelse
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card card-soft p-3 h-100">
      <h5 class="section-title mb-3"><i class="fa-solid fa-users me-2 text-primary"></i>Students per Class</h5>
      @forelse($byClass as $row)
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span>Class {{ $row->class_no }}</span>
          <div class="flex-grow-1 mx-3" style="height:8px;background:#f1f5f9;border-radius:8px;overflow:hidden;">
            <div style="height:100%;width:{{ $totalStudents ? round(($row->total / $totalStudents) * 100) : 0 }}%;background:var(--gradient-primary,#0f766e);border-radius:8px;"></div>
          </div>
          <b>{{ $row->total }}</b>
        </div>
      @empty
        <div class="muted small">Abhi koi student add nahi hua — <a href="{{ url('admin/students') }}">Students section</a> se add karein.</div>
      @endforelse

      <hr />
      <h5 class="section-title mb-2"><i class="fa-solid fa-money-bill-wave me-2 text-success"></i>Latest Payments</h5>
      @forelse($recentPayments as $p)
        <div class="d-flex justify-content-between" style="font-size:13px;padding:6px 0;">
          <span>{{ $p->student?->name ?? '—' }}</span>
          <b>Rs. {{ number_format($p->amount) }} <span class="muted" style="font-weight:400;">({{ $p->method }})</span></b>
        </div>
      @empty
        <div class="muted small">Koi payment abhi nahi hui.</div>
      @endforelse
    </div>
  </div>
</div>
@endsection
