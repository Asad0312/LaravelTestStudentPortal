<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>ID Card — {{ $student->name }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #e5e7eb;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    padding: 24px 12px 40px;
  }
  .id-card {
    width: 85.6mm;
    height: 53.98mm;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
    position: relative;
    border: 1px solid rgba(15, 23, 42, 0.08);
  }
  .id-header {
    height: 16mm;
    background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 10px;
    color: #fff;
  }
  .logo-circle {
    width: 9mm;
    height: 9mm;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.22);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
  }
  .header-text .title {
    font-weight: 800;
    font-size: 11px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
  }
  .header-text .sub {
    font-size: 6.5px;
    opacity: 0.9;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    margin-top: 1px;
  }
  .id-body {
    display: flex;
    gap: 9px;
    padding: 7px 10px 0 10px;
    align-items: flex-start;
  }
  .photo-frame {
    width: 19mm;
    height: 24mm;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid rgba(15, 118, 110, 0.4);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.6);
    flex-shrink: 0;
  }
  .photo-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .student-name {
    font-weight: 800;
    font-size: 10px;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.02em;
  }
  .id-pill {
    align-self: flex-start;
    background: rgba(15, 118, 110, 0.12);
    color: #0f766e;
    font-weight: 700;
    font-size: 7px;
    padding: 1px 6px;
    border-radius: 999px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
  }
  .details {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .detail-row {
    display: flex;
    justify-content: space-between;
    font-size: 7px;
    color: #475569;
  }
  .detail-row b {
    color: #0f172a;
    font-weight: 600;
    text-align: right;
    max-width: 38mm;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .qr-section {
    position: absolute;
    right: 9px;
    bottom: 7px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
  }
  .qr-box {
    width: 14mm;
    height: 14mm;
    border-radius: 6px;
    border: 1px solid rgba(148, 163, 184, 0.6);
    padding: 1px;
    background: #fff;
  }
  .qr-box img {
    width: 100%;
    height: 100%;
    display: block;
  }
  .qr-text {
    font-size: 5.5px;
    color: #475569;
    letter-spacing: 0.16em;
    text-transform: uppercase;
  }
  .id-footer {
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 5mm;
    background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 6px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
  }
  .actions {
    display: flex;
    gap: 10px;
  }
  .actions button,
  .actions a {
    font-family: Inter, sans-serif;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    padding: 10px 18px;
    border-radius: 999px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .btn-print {
    background: linear-gradient(90deg, #0f766e, #14b8a6);
    color: #fff;
    box-shadow: 0 14px 30px rgba(15, 118, 110, 0.18);
  }
  .btn-back {
    background: #fff;
    color: #0f766e;
    border: 1px solid rgba(20, 184, 166, 0.4) !important;
  }

  @media print {
    body {
      background: #fff;
      padding: 0;
    }
    .actions {
      display: none !important;
    }
    .id-card {
      margin: 10mm auto;
      box-shadow: none;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    @page {
      size: auto;
      margin: 8mm;
    }
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
  <div class="id-header">
    <div class="logo-circle">
      <i class="fa-solid fa-graduation-cap"></i>
    </div>
    <div class="header-text">
      <div class="title">IRAA</div>
      <div class="sub">Iqra Riffat ull Atfal Academy</div>
    </div>
  </div>
  <div class="id-body">
    <div class="photo-frame">
      <img src="{{ $avatar }}" alt="{{ $student->name }}">
    </div>
    <div class="info">
      <div class="student-name">{{ Str::limit($student->name, 26) }}</div>
      <div class="id-pill">ID: {{ $student->student_id }}</div>
      <div class="details">
        <div class="detail-row">
          <span>Father Name</span>
          <b>{{ Str::limit($student->father_name ?: '—', 18) }}</b>
        </div>
        <div class="detail-row">
          <span>Class</span>
          <b>{{ $cls }}</b>
        </div>
        <div class="detail-row">
          <span>Phone</span>
          <b>{{ $student->whatsapp ?: $student->phone ?: '—' }}</b>
        </div>
        <div class="detail-row">
          <span>Session</span>
          <b>{{ $student->batch ?: 'Session '.date('Y') }}</b>
        </div>
      </div>
    </div>
  </div>
  <div class="qr-section">
    <div class="qr-box">
      <img src="{{ $qr }}" alt="QR Code">
    </div>
    <div class="qr-text">Scan for Attendance</div>
  </div>
  <div class="id-footer">Student Identity Card</div>
</div>

<div class="actions">
  <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save PDF</button>
  <a class="btn-back" href="{{ route('portal.show') }}"><i class="fa-solid fa-arrow-left"></i> Back to Portal</a>
</div>
</body>
</html>
