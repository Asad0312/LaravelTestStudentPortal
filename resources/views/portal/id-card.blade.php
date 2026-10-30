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
    background: #cbd5e1;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 24px 12px 40px;
  }
  .id-card {
    width: 85.6mm;
    height: 53.98mm;
    background: #ffffff;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 30px 40px -28px rgba(15, 23, 42, 0.9);
    border: 1px solid rgba(15, 23, 42, 0.12);
  }
  .header {
    height: 14.5mm;
    background: linear-gradient(90deg, #0f766e 0%, #22d3ee 100%);
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 0 9px;
    color: #fff;
  }
  .logo {
    width: 9mm;
    height: 9mm;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.24);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
  }
  .school-text { min-width: 0; }
  .school-text .name {
    font-weight: 800;
    font-size: 10px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    line-height: 1;
  }
  .school-text .tagline {
    font-size: 6px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    opacity: 0.92;
    margin-top: 2px;
    white-space: nowrap;
  }
  .content {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    padding: 5px 9px 0;
    height: calc(53.98mm - 14.5mm - 4.2mm);
  }
  .photo {
    width: 19mm;
    height: 24mm;
    border-radius: 4px;
    overflow: hidden;
    border: 1px solid rgba(15, 118, 110, 0.6);
    flex-shrink: 0;
    background: #f1f5f9;
  }
  .photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .student-name {
    font-weight: 800;
    font-size: 9.2px;
    text-transform: uppercase;
    color: #0f172a;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .id-pill {
    align-self: flex-start;
    background: rgba(20, 184, 166, 0.16);
    color: #0f766e;
    font-weight: 700;
    font-size: 6.2px;
    padding: 1px 6px;
    border-radius: 999px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    white-space: nowrap;
  }
  .rows {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-top: 1px;
  }
  .row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 4px;
    font-size: 6.8px;
    line-height: 1.15;
  }
  .row .label {
    color: #475569;
    white-space: nowrap;
  }
  .row .value {
    color: #0f172a;
    font-weight: 600;
    text-align: right;
    max-width: 30mm;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .qr {
    flex-shrink: 0;
    align-self: flex-end;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;
  }
  .qr-box {
    width: 13mm;
    height: 13mm;
    border: 1px solid rgba(148, 163, 184, 0.9);
    border-radius: 3px;
    padding: 1px;
    background: #fff;
  }
  .qr-box img {
    width: 100%;
    height: 100%;
    display: block;
  }
  .qr-text {
    font-size: 4.8px;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: #475569;
    white-space: nowrap;
  }
  .footer {
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 4.2mm;
    background: linear-gradient(90deg, #0f766e 0%, #22d3ee 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 5px;
    letter-spacing: 0.36em;
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
    padding: 9px 18px;
    border-radius: 999px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .btn-print {
    background: linear-gradient(90deg, #0f766e, #22d3ee);
    color: #fff;
    box-shadow: 0 16px 30px rgba(15, 118, 110, 0.18);
  }
  .btn-back {
    background: #fff;
    color: #0f766e;
    border: 1px solid rgba(20, 184, 166, 0.5) !important;
  }
  @media print {
    body { background: #fff; padding: 0; }
    .actions { display: none !important; }
    .id-card {
      margin: 8mm auto;
      box-shadow: none;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    @page { margin: 6mm; size: auto; }
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
  <div class="header">
    <div class="logo">
      <i class="fa-solid fa-graduation-cap"></i>
    </div>
    <div class="school-text">
      <div class="name">IRAA</div>
      <div class="tagline">Iqra Riffat ull Atfal Academy</div>
    </div>
  </div>

  <div class="content">
    <div class="photo">
      <img src="{{ $avatar }}" alt="{{ $student->name }}">
    </div>

    <div class="info">
      <div class="student-name">{{ Str::limit($student->name, 28) }}</div>
      <div class="id-pill">ID: {{ $student->student_id }}</div>
      <div class="rows">
        <div class="row">
          <span class="label">Father</span>
          <span class="value">{{ Str::limit($student->father_name ?: '—', 20) }}</span>
        </div>
        <div class="row">
          <span class="label">Class</span>
          <span class="value">{{ $cls }}</span>
        </div>
        <div class="row">
          <span class="label">Phone</span>
          <span class="value">{{ $student->whatsapp ?: $student->phone ?: '—' }}</span>
        </div>
        <div class="row">
          <span class="label">Session</span>
          <span class="value">{{ $student->batch ?: 'Session '.date('Y') }}</span>
        </div>
      </div>
    </div>

    <div class="qr">
      <div class="qr-box">
        <img src="{{ $qr }}" alt="QR Code">
      </div>
      <div class="qr-text">Scan</div>
    </div>
  </div>

  <div class="footer">Student Identity Card</div>
</div>

<div class="actions">
  <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save PDF</button>
  <a class="btn-back" href="{{ route('portal.show') }}"><i class="fa-solid fa-arrow-left"></i> Back to Portal</a>
</div>
</body>
</html>
