<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fee Slip - {{ $student->student_id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif}
        body{background:#f8fafc;color:#0f172a}
        .slip{width:800px;margin:30px auto;background:#fff;border-radius:16px;box-shadow:0 30px 60px -40px rgba(15,23,42,.9);overflow:hidden;border:1px solid #e5e7eb}
        .head{display:flex;align-items:center;justify-content:space-between;padding:24px 32px;background:linear-gradient(90deg,#0f766e,#0ea5e9)}
        .brand{display:flex;align-items:center;gap:14px;color:#fff}
        .logo{width:56px;height:56px;border-radius:14px;background:rgba(255,255,255,.2);display:grid;place-items:center;font-size:24px}
        .title{font-weight:700;font-size:18px;letter-spacing:.08em;text-transform:uppercase}
        .sub{opacity:.9;font-size:12px;margin-top:4px}
        .meta{text-align:right;color:#fff}
        .meta small{display:block;opacity:.8}
        .meta b{font-size:16px}
        .body{padding:28px 32px;display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
        .card{border:1px solid #e5e7eb;border-radius:12px;padding:18px}
        .card h4{font-size:14px;margin-bottom:12px;display:flex;align-items:center;gap:8px}
        .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed #e5e7eb;font-size:13px}
        .row:last-child{border-bottom:none}
        .row span{color:#475569}
        .table{grid-column:1/-1;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
        .table table{width:100%;border-collapse:collapse}
        .table thead{background:#f8fafc}
        .table th,.table td{padding:12px 18px;text-align:left;font-size:13px;border-bottom:1px solid #f1f5f9}
        .table th{font-weight:600;color:#475569}
        .summary{grid-column:1/-1;display:flex;justify-content:flex-end}
        .sum{width:320px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
        .sum-row{display:flex;justify-content:space-between;padding:10px 18px;font-size:13px;border-bottom:1px solid #f1f5f9}
        .sum-row.total{background:#f8fafc;font-weight:600}
        .foot{padding:18px 32px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#475569}
        .sign{display:flex;flex-direction:column;align-items:center;gap:6px}
        .line{width:220px;border-bottom:1px solid #cbd5f5}
        @media print{body{background:#fff}.slip{margin:0;box-shadow:none;border:none}.no-print{display:none}}
    </style>
</head>
<body>
    <div class="slip">
        <div class="head">
            <div class="brand">
                <div class="logo"><i class="fa-solid fa-graduation-cap"></i></div>
                <div>
                    <div class="title">IRAA</div>
                    <div class="sub">Iqra Riffat ull Atfal Academy</div>
                </div>
            </div>
            <div class="meta">
                <small>FEE SLIP</small>
                <b>{{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</b>
                <small>Print Date: {{ now()->format('d-M-Y h:i A') }}</small>
            </div>
        </div>
        <div class="body">
            <div class="card">
                <h4><i class="fa-solid fa-user-graduate"></i> Student Details</h4>
                <div class="row"><span>Student ID</span><b>{{ $student->student_id }}</b></div>
                <div class="row"><span>Student Name</span><b>{{ $student->name }}</b></div>
                <div class="row"><span>Father Name</span><b>{{ $student->father_name }}</b></div>
                <div class="row"><span>Class</span><b>{{ method_exists($student,'getDisplayClassAttribute') ? $student->display_class : ('Class '.($student->class_level??$student->class_no).'-'.($student->madarsa_section??$student->section)) }}</b></div>
                <div class="row"><span>Phone</span><b>{{ $student->phone }}</b></div>
            </div>
            <div class="card">
                <h4><i class="fa-solid fa-file-invoice"></i> Fee Details</h4>
                <div class="row"><span>Slip For</span><b>{{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</b></div>
                <div class="row"><span>Monthly Fee</span><b>Rs. {{ number_format($monthly) }}</b></div>
                <div class="row"><span>Total Paid</span><b>Rs. {{ number_format($paid) }}</b></div>
                <div class="row"><span>Remaining</span><b>Rs. {{ number_format($remaining) }}</b></div>
                <div class="row"><span>Status</span><b>{{ $remaining <= 0 ? 'PAID' : ($paid > 0 ? 'PENDING' : 'UNPAID') }}</b></div>
            </div>
            <div class="table">
                <table>
                    <thead><tr><th>Date</th><th>Amount</th><th>Method</th></tr></thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr><td>{{ $p->paid_on?->format('d-M-Y') }}</td><td>Rs. {{ number_format($p->amount) }}</td><td>{{ $p->method }}</td></tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;color:#94a3b8">No payments recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="summary">
                <div class="sum">
                    <div class="sum-row"><span>Monthly Fee</span><span>Rs. {{ number_format($monthly) }}</span></div>
                    <div class="sum-row"><span>Total Paid</span><span>Rs. {{ number_format($paid) }}</span></div>
                    <div class="sum-row total"><span>Balance</span><span>Rs. {{ number_format($remaining) }}</span></div>
                </div>
            </div>
        </div>
        <div class="foot">
            <div>
                <b>IRAA - Iqra Riffat ull Atfal Academy</b><br>
                <span>Generated from StudentHub System</span>
            </div>
            <div class="sign">
                <div class="line"></div>
                <span>Authorized Signature</span>
            </div>
        </div>
    </div>
    <div class="no-print" style="text-align:center;margin:16px">
        <button onclick="window.print()" style="padding:10px 18px;border-radius:10px;border:none;background:linear-gradient(90deg,#0f766e,#0ea5e9);color:#fff;cursor:pointer"><i class="fa-solid fa-print"></i> Print Slip</button>
        <button onclick="window.close()" style="padding:10px 18px;border-radius:10px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;margin-left:8px">Close</button>
    </div>
</body>
</html>
