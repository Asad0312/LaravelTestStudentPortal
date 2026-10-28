@extends('layouts.app')

@section('title', 'IRAA — Login')

@section('content')
<div class="login-wrap">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>
  <div class="grid-overlay"></div>

  <div class="login-card">
    <div class="login-brand anim-fade-up">
      <div class="brand-badge">
        <div class="brand-logo"><i class="fa-solid fa-graduation-cap"></i></div>
        IRAA
      </div>
      <h1>Iqra Riffat ull Atfal Academy<br /><span>School + Madarsa Management System</span></h1>

      <ul class="features-list">
        <li><i class="fa-solid fa-id-card"></i> Auto Student ID + Unique QR Code</li>
        <li><i class="fa-solid fa-qrcode"></i> One-Scan QR Attendance</li>
        <li><i class="fa-solid fa-money-bill-wave"></i> Fees &amp; Payment Tracking</li>
        <li><i class="fa-solid fa-chart-line"></i> Live Reports &amp; Analytics</li>
      </ul>

      <div class="qr-preview">
        <div class="mini-qr"><i class="fa-solid fa-qrcode"></i></div>
        <div class="stat-mini">
          <b>Student Portal</b>
          Har student apni ID + password se login karta hai
        </div>
      </div>
    </div>

    <div class="login-form anim-fade-up d2">
      <h3 class="fw-bold mb-1">Welcome back 👋</h3>
      <p class="muted mb-0" style="font-size:13.5px;">Apni role choose karein aur login karein</p>

      <div class="role-switch">
        <button type="button" class="{{ $role !== 'student' ? 'active' : '' }}" data-role="admin"><i class="fa-solid fa-user-shield me-1"></i> Admin</button>
        <button type="button" class="{{ $role === 'student' ? 'active' : '' }}" data-role="student"><i class="fa-solid fa-user-graduate me-1"></i> Student</button>
      </div>

      <div class="err-box {{ $errors->any() ? 'show' : '' }}" id="errBox"><i class="fa-solid fa-circle-exclamation me-1"></i><span id="errMsg">{{ $errors->first() }}</span></div>

      {{-- ===== ADMIN FORM (email + password) ===== --}}
      <form method="post" action="{{ route('login.attempt') }}" id="adminForm" style="display:{{ $role === 'student' ? 'none' : '' }};">
        @csrf
        <input type="hidden" name="role" value="admin" />
        <div class="field">
          <i class="fa-solid fa-envelope"></i>
          <input class="form-ctrl" type="email" name="email" placeholder="Admin email" value="{{ old('email') }}" required />
        </div>
        <div class="field">
          <i class="fa-solid fa-lock"></i>
          <input class="form-ctrl" type="password" name="password" placeholder="Password" required />
          <button type="button" class="toggle-eye" onclick="togglePass(this)"><i class="fa-regular fa-eye"></i></button>
        </div>

        <button type="submit" class="btn-primary-grad">
          <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Admin Login
        </button>
      </form>

      {{-- ===== STUDENT FORM (Student ID + password — email se login nahi) ===== --}}
      <form method="post" action="{{ route('login.attempt') }}" id="studentForm" style="display:{{ $role === 'student' ? '' : 'none' }};">
        @csrf
        <input type="hidden" name="role" value="student" />
        <div class="field">
          <i class="fa-solid fa-id-card"></i>
          <input class="form-ctrl" type="text" name="student_id" placeholder="Student ID — e.g. STU-1001" autocomplete="off" value="{{ old('student_id') }}" required />
        </div>
        <div class="field">
          <i class="fa-solid fa-lock"></i>
          <input class="form-ctrl" type="password" name="password" placeholder="Password" required />
          <button type="button" class="toggle-eye" onclick="togglePass(this)"><i class="fa-regular fa-eye"></i></button>
        </div>

        <button type="submit" class="btn-primary-grad" style="background:linear-gradient(135deg,#0ea5e9,#0d9488);background-size:150% 150%;">
          <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Open My Portal
        </button>

        <div class="demo-hint text-start" style="background:var(--bg-soft);">
          <b style="color:var(--primary-dark);">Student login kaise karein?</b>
          <div style="font-size:12.5px;line-height:1.7;margin-top:4px;">
            Apni <b>Student ID</b> (jaise STU-1001) aur apna password daalein.<br />
            Email se login nahi hota — sirf ID + password.
          </div>
        </div>
      </form>

      <div class="demo-hint" id="adminHint" style="display:{{ $role === 'student' ? 'none' : '' }};">
        <i class="fa-solid fa-circle-info me-1"></i> Admin panel me students, tests, marks, fees sab manage hota hai.
      </div>
    </div>
  </div>
</div>

<script>
  function togglePass(btn) {
    const inp = btn.parentElement.querySelector('input');
    const ic = btn.querySelector('i');
    if (inp.type === 'password') { inp.type = 'text'; ic.className = 'fa-regular fa-eye-slash'; }
    else { inp.type = 'password'; ic.className = 'fa-regular fa-eye'; }
  }

  document.querySelectorAll('.role-switch button').forEach(b => {
    b.addEventListener('click', () => {
      document.querySelectorAll('.role-switch button').forEach(x => x.classList.remove('active'));
      b.classList.add('active');
      document.getElementById('errBox').classList.remove('show');
      const isStudent = b.dataset.role === 'student';
      document.getElementById('adminForm').style.display = isStudent ? 'none' : '';
      document.getElementById('studentForm').style.display = isStudent ? '' : 'none';
      document.getElementById('adminHint').style.display = isStudent ? 'none' : '';
    });
  });
</script>
@endsection
