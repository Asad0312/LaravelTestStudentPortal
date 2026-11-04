<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>ID Card — {{ $student->name }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: Inter, Arial, sans-serif; }
  body {
    background: #d1d5db;
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
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 30px 40px -28px rgba(15, 23, 42, 0.95);
    border: 1px solid rgba(15, 23, 42, 0.14);
  }
  .header {
    height: 14mm;
    background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
    display: table;
    width: 100%;
  }
  .header-inner {
    display: table-row;
  }
  .logo-cell {
    display: table-cell;
    width: 10mm;
    vertical-align: middle;
    padding-left: 8px;
  }
  .logo {
    width: 8.5mm;
    height: 8.5mm;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.26);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 13px;
  }
  .school-cell {
    display: table-cell;
    vertical-align: middle;
    padding-left: 6px;
  }
  .school-name {
    font-weight: 800;
    font-size: 9.8px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: #fff;
    line-height: 1;
  }
  .school-sub {
    font-size: 5.6px;
    letter-spacing: 0.38em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.92);
    margin-top: 2px;
  }
  .main {
    position: relative;
    height: calc(53.98mm - 14mm - 4mm);
    padding: 4px 7px 0 7px;
  }
  .photo {
    position: absolute;
    left: 7px;
    top: 4px;
    width: 18.5mm;
    height: 23.5mm;
    border-radius: 3px;
    overflow: hidden;
    border: 1px solid rgba(15, 118, 110, 0.65);
    background: #f8fafc;
  }
  .photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .details {
    margin-left: 21.5mm;
    margin-right: 15mm;
  }
  .student-name {
    font-weight: 800;
    font-size: 9px;
    text-transform: uppercase;
    color: #0f172a;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .id-pill {
    display: inline-block;
    margin-top: 1px;
    background: rgba(20, 184, 166, 0.18);
    color: #0f766e;
    font-weight: 700;
    font-size: 5.8px;
    padding: 0 5px;
    border-radius: 999px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
  }
  .info-table {
    margin-top: 2px;
    width: 100%;
  }
  .info-table td {
    font-size: 6.6px;
    padding: 1px 0;
    vertical-align: top;
  }
  .info-table .label {
    color: #475569;
    width: 16mm;
  }
  .info-table .value {
    color: #0f172a;
    font-weight: 600;
    text-align: right;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 24mm;
  }
  .qr {
    position: absolute;
    right: 6px;
    top: 10px;
    text-align: center;
  }
  .qr-box {
    width: 12.5mm;
    height: 12.5mm;
    border: 1px solid rgba(148, 163, 184, 0.95);
    border-radius: 2px;
    padding: 1px;
    background: #fff;
    margin: 0 auto;
  }
  .qr-box img {
    width: 100%;
    height: 100%;
    display: block;
  }
  .qr-text {
    font-size: 4.6px;
    text-transform: uppercase;
    letter-spacing: 0.24em;
    color: #475569;
    margin-top: 1px;
  }
  .footer {
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 4mm;
    background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 4.8px;
    letter-spacing: 0.42em;
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
    <div class="header-inner">
      <div class="logo-cell">
        <div class="logo"><i class="fa-solid fa-graduation-cap"></i></div>
      </div>
      <div class="school-cell">
        <div class="school-name">IRAA</div>
        <div class="school-sub">Iqra Riffat ull Atfal Academy</div>
      </div>
    </div>
  </div>

  <div class="main">
    <div class="photo">
      <img src="{{ $avatar }}" alt="{{ $student->name }}">
    </div>
    <div class="details">
      <div class="student-name">{{ $student->name }}</div>
      <div class="id-pill">STUDENT ID • {{ $student->student_id }}</div>
      <table class="info-table" cellspacing="0" cellpadding="0">
        <tr>
          <td class="label">Father Name</td>
          <td class="value">{{ $student->father_name ?: '—' }}</td>
        </tr>
        <tr>
          <td class="label">Class</td>
          <td class="value">{{ $cls }}</td>
        </tr>
        <tr>
          <td class="label">Contact</td>
          <td class="value">{{ $student->whatsapp ?: $student->phone ?: '—' }}</td>
        </tr>
        <tr>
          <td class="label">Session</td>
          <td class="value">{{ $student->batch ?: 'Session '.date('Y') }}</td>
        </tr>
      </table>
    </div>
    <div class="qr">
      <div class="qr-box">
        <img src="{{ $qr }}" alt="QR">
      </div>
      <div class="qr-text">SCAN</div>
    </div>
  </div>

  <div class="footer">STUDENT IDENTITY CARD</div>
</div>

<div class="actions">
  <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save PDF</button>
  <a class="btn-back" href="{{ route('portal.show') }}"><i class="fa-solid fa-arrow-left"></i> Back to Portal</a>
</div>
</body>
</html>
