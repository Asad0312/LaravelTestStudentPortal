@extends('layouts.admin')

@section('page_title', 'QR Attendance')
@section('page_sub', 'ID card ka QR scan karein — attendance auto mark ho jayegi')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush

@section('content')
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card card-soft p-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="section-title mb-0"><i class="fa-solid fa-qrcode me-2 text-success"></i>Camera Scanner</h5>
        <span class="badge bg-soft-primary">{{ $today }}</span>
      </div>

      <div id="reader" style="width:100%;border-radius:14px;overflow:hidden;"></div>

      <div id="camHint" class="muted small mt-2">
        Camera allow karein aur student ka ID card QR camera ke samne rakhein.
      </div>

      <hr class="my-3" />

      <form id="manualForm" class="d-flex flex-wrap gap-2">
        <input class="form-ctrl" id="manualId" name="student_id" placeholder="Manual — e.g. STU-1001" style="min-width:200px;" />
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;font-size:13px;">
          <i class="fa-solid fa-check me-1"></i> Mark Present
        </button>
      </form>
    </div>
  </div>

  <div class="col-lg-6">
    <div id="resultBox" class="card card-soft p-3 mb-3" style="display:none;">
      <div id="resultInner"></div>
    </div>

    <div class="card card-soft p-3">
      <h5 class="section-title mb-3"><i class="fa-solid fa-clock-rotate-left me-2"></i>Aaj ke QR Scans</h5>
      <div id="scanList">
        @forelse($scans as $row)
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <div>
              <b style="font-size:13.5px;">{{ $row->student?->name ?? 'Student' }}</b>
              <div class="muted" style="font-size:11.5px;">{{ $row->student?->student_id }} — Class {{ $row->student?->class_no }}-{{ $row->student?->section }}</div>
            </div>
            <span class="badge bg-soft-success">{{ $row->check_in_at ? date('h:i A', strtotime($row->check_in_at)) : 'Present' }}</span>
          </div>
        @empty
          <div class="muted text-center py-3" id="emptyScans">Abhi koi scanner nahi hua.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const url  = "{{ route('admin.attendance.scan') }}";
  const box  = document.getElementById('resultBox');
  const inner = document.getElementById('resultInner');
  const list = document.getElementById('scanList');
  let busy = false;
  let lastCode = '';
  let lastAt = 0;

  function show(ok, data) {
    box.style.display = 'block';
    const cls = ok ? (data.already ? 'flash flash-warn' : (data.updated ? 'flash flash-info' : 'flash flash-ok')) : 'flash flash-err';
    const icon = ok ? (data.already ? 'fa-circle-exclamation' : (data.updated ? 'fa-arrows-rotate' : 'fa-circle-check')) : 'fa-circle-xmark';
    if (ok) {
      inner.innerHTML =
        '<div class="' + cls + '"><i class="fa-solid ' + icon + '"></i> <div><b>' + data.name + '</b> (' + data.student_id +
        ') — ' + data.class + ' • ' + data.time + '<div style="font-size:12px;margin-top:2px;">' + (data.message||'') + '</div></div></div>';
      if(!data.already){ prependScan(data); }
    } else {
      inner.innerHTML = '<div class="' + cls + '"><i class="fa-solid ' + icon + '"></i> ' + (data.error||'Error') + '</div>';
    }
  }

  function prependScan(data) {
    const empty = document.getElementById('emptyScans');
    if (empty) empty.remove();
    const row = document.createElement('div');
    row.className = 'd-flex justify-content-between align-items-center py-2 border-bottom';
    row.innerHTML =
      '<div><b style="font-size:13.5px;">' + data.name + '</b>' +
      '<div class="muted" style="font-size:11.5px;">' + data.student_id + '</div></div>' +
      '<span class="badge bg-soft-success">' + data.time + '</span>';
    list.prepend(row);
  }

  async function mark(code) {
    const now = Date.now();
    if (busy) return;
    if (code === lastCode && now - lastAt < 4000) return;
    lastCode = code; lastAt = now; busy = true;

    try {
      const fd = new FormData();
      fd.append('student_id', code);
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: fd,
      });
      const data = await res.json();
      show(data.ok === true, data);
    } catch (e) {
      show(false, { error: 'Network error — dobara koshish karein.' });
    } finally {
      setTimeout(function () { busy = false; }, 1200);
    }
  }

  document.getElementById('manualForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const v = document.getElementById('manualId').value.trim();
    if (v) { mark(v); document.getElementById('manualId').value = ''; }
  });

  if (typeof Html5QrcodeScanner === 'undefined') return;

  const scanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: { width: 240, height: 240 } }, false);
  scanner.render(function (text) { mark(text.trim()); }, function () {});
})();
</script>
@endpush
