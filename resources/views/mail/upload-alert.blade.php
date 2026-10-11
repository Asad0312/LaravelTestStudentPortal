<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:24px;background:#f1f5fb;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:560px;margin:auto;background:#fff;border-radius:14px;padding:24px;border:1px solid #e2e8f0;">
    <div style="font-size:18px;font-weight:bold;color:#0f766e;margin-bottom:4px;">🎓 {{ config('app.name') }}</div>
    <div style="font-size:12px;color:#64748b;margin-bottom:16px;">Student Management &amp; QR Attendance System</div>

    <h2 style="margin:0 0 10px;font-size:16px;color:#0b1220;">{{ $subjectTitle }}</h2>
    <p style="font-size:14px;line-height:1.7;color:#334155;white-space:pre-line;">{{ $body }}</p>

    <hr style="border:0;border-top:1px solid #e2e8f0;margin:18px 0;" />
    <p style="font-size:12px;color:#94a3b8;margin:0;">
      Yeh alert system se apne aap bheja gaya hai ({{ $audience }}).
      Portal login: {{ config('app.url') }}/login
    </p>
  </div>
</body>
</html>
