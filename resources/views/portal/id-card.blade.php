<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>ID Card — {{ $student->name }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; background: #eef2f6;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    display: flex; flex-direction: column; align-items: center; gap: 18px; padding: 28px 16px 60px;
  }
  .id-card {
    width: 340px; background: #fff; border-radius: 18px; overflow: hidden;
    box-shadow: 0 18px 40px rgba(11,18,32,.18);
  }
  .id-head {
    background: linear-gradient(135deg,#0b1220,#0f766e,#f59e0b);
    color: #fff; padding: 14px 16px; display: flex; align-items: center; gap: 10px;
  }
  .id-head .logo {
    width: 38px; height: 38px; border-radius: 11px; background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.3); display: grid; place-items: center; font-size: 17px;
  }
  .id-head .school { font-weight: 800; font-size: 15px; line-height: 1.15; }
  .id-head .sub { font-size: 10.5px; opacity: .85; letter-spacing: .4px; text-transform: uppercase; }
  .id-body { padding: 16px; display: flex; gap: 14px; }
  .id-photo {
    width: 92px; height: 92px; border-radius: 14px; object-fit: cover;
    border: 3px solid #e2e8f0; background: #f1f5f9; flex-shrink: 0;
  }
  .id-name { font-size: 16px; font-weight: 800; color: #0b1220; line-height: 1.2; }
  .id-role { font-size: 11px; color: #64748b; margin-bottom: 8px; }
  .id-row { font-size: 11.5px; color: #334155; margin-top: 4px; }
  .id-row b { color: #0b1220; }
  .id-foot {
    border-top: 1px dashed #cbd5e1; padding: 12px 16px;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
  }
  .qr { width: 62px; height: 62px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; padding: 3px; }
  .id-foot .tiny { font-size: 10px; color: #64748b; line-height: 1.5; }
  .chip {
    display: inline-block; background: #0f766e; color: #fff; font-weight: 700;
    font-size: 12px; padding: 4px 10px; border-radius: 999px; letter-spacing: .5px;
  }
  .actions { display: flex; gap: 10px; }
  .actions button, .actions a {
    font-family: inherit; font-size: 13px; font-weight: 600; border: 0; cursor: pointer;
    padding: 10px 18px; border-radius: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
  }
  .btn-print { background: linear-gradient(135deg,#0f766e,#0d9488); color: #fff; }
  .btn-back  { background: #fff; color: #0f766e; border: 1px solid #99f6e4 !important; }

  @media print {
    body { background: #fff; padding: 0; display: block; }
    .actions { display: none !important; }
    .id-card {
      width: 340px; margin: 24px auto; box-shadow: none; border: 1px solid #cbd5e1;
      -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .id-head { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { margin: 12mm; }
  }
</style>
</head>
<body>

@php
    $avatar = 'https://api.dicebear.com/8.x/initials/svg?seed=' . urlencode($student->avatar_seed ?: $student->name);
    $qr = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($student->student_id);
    $cls = method_exists($student, 'getDisplayClassAttribute') ? $student->display_class : 'Class '.$student->class_no.'-'.$student->section;
@endphp

<div class="id-card">
  <div class="id-head">
    <div class="logo-circle"><i class="fa-solid fa-graduation-cap"></i></div>
    <h1>IRAA</h1>
    <p>Iqra Riffat ull Atfal Academy</p>
  </div>
  <div class="photo-wrap">
    <div class="photo"><img src="{{ $avatar }}" alt=""></div>
  </div>
  <div class="id-body">
    <div class="name">{{ $student->name }}</div>
    <div class="id-badge">STUDENT ID • {{ $student->student_id }}</div>
    <div class="details">
      <div class="detail-row"><span>Father Name</span><b>{{ $student->father_name ?: '—' }}</b></div>
      <div class="detail-row"><span>Class</span><b>{{ $cls }}</b></div>
      <div class="detail-row"><span>WhatsApp/Phone</span><b>{{ $student->whatsapp ?: $student->phone ?: '—' }}</b></div>
      <div class="detail-row"><span>Address</span><b style="text-align:right;max-width:160px;">{{ $student->address ?: '—' }}</b></div>
    </div>
    <div class="qr-wrap">
      <div class="qr"><img src="{{ $qr }}" alt=""></div>
      <small style="color:#475569;">Scan for attendance</small>
    </div>
  </div>
  <div class="id-foot">Yeh card hamesha saath rakhein</div>
</div>

<div class="actions">
  <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save PDF</button>
  <a class="btn-back" href="{{ route('portal.show') }}"><i class="fa-solid fa-arrow-left"></i> Back to Portal</a>
</div>

<script>
  window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 700); });
</script>
</body>
</html>
