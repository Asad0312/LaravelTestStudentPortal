<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>@yield('title', 'Admin') — IRAA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
<style>
  .flash{display:flex;gap:10px;align-items:center;padding:12px 16px;border-radius:12px;font-size:13.5px;margin-bottom:16px;animation:fadeIn .3s ease;}
  .flash-ok{background:#ecfdf5;border:1px solid #6ee7b7;color:#047857;}
  .flash-err{background:#fff1f2;border:1px solid #fda4af;color:#be123c;}
  .topbar .page-title h2{font-size:20px;}
  @media(max-width:991px){ .sidebar{transform:translateX(-100%);} .sidebar.open{transform:none;} }
</style>
@stack('head')
</head>
<body>

@php
    $seg   = request()->segment(2) ?? 'dashboard';
    $admin = \App\Models\Admin::find(session('admin_id'));
    $nav = [
        'dashboard'   => ['admin',                'fa-gauge-high',      'Dashboard'],
        'students'    => ['admin/students',       'fa-users',           'Students'],
        'admissions'  => ['admin/admissions',     'fa-user-plus',       'Admissions'],
        'lookup'      => ['admin/lookup',         'fa-magnifying-glass','Student Lookup'],
        'attendance'  => ['admin/attendance',     'fa-calendar-check',  'Attendance'],
        'scan'        => ['admin/scan',           'fa-qrcode',          'QR Attendance'],
        'fees'        => ['admin/fees',           'fa-money-bill-wave', 'Fee Management'],
        'exams'       => ['admin/exams',          'fa-file-lines',      'Exams & Fees'],
        'tests'       => ['admin/tests',          'fa-list-check',      'Test Plan'],
        'marks'       => ['admin/marks',          'fa-square-poll-vertical', 'Marks'],
        'books'       => ['admin/books',          'fa-book',            'Books'],
        'messages'    => ['admin/messages',       'fa-bullhorn',        'Messages'],
        'settings'    => ['admin/settings',       'fa-gear',            'Settings'],
    ];
@endphp

<div class="app-shell">

  <aside class="sidebar" id="sidebar">
    <div class="side-head">
      <div class="brand-logo"><i class="fa-solid fa-school"></i></div>
      <div>
        <div class="brand-name">IRAA</div>
        <div class="brand-sub">Iqra Riffat ull Atfal Academy</div>
      </div>
    </div>

    <nav class="side-nav">
      <div class="nav-section-title">Main</div>
      @foreach($nav as $key => [$route, $icon, $label])
        <a class="nav-link-item {{ $seg === $key ? 'active' : '' }}" href="{{ url($route) }}">
          <i class="fa-solid {{ $icon }}"></i> {{ $label }}
        </a>
      @endforeach
    </nav>

    <div class="side-foot">
      <form method="post" action="{{ route('logout') }}" class="m-0">
        @csrf
        <button type="submit" class="backup-chip text-decoration-none w-100 border-0" style="color:inherit;background:none;">
          <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
        </button>
      </form>
    </div>
  </aside>

  <div class="main-area">
    <header class="topbar">
      <button class="icon-btn menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
      <div class="page-title">
        <h2 class="title-underline">@yield('page_title', 'Dashboard')</h2>
        <small>@yield('page_sub', date('l, j F Y'))</small>
      </div>
      <div class="topbar-actions">
        <div class="avatar-chip">
          <img class="avatar" src="https://api.dicebear.com/8.x/initials/svg?seed={{ urlencode($admin?->name ?? 'Admin') }}" alt="admin" />
          <div class="d-none d-md-block">
            <b style="font-size:13.5px;">{{ $admin?->name ?? 'Admin' }}</b><br />
            <span class="muted" style="font-size:11.5px;">{{ $admin?->role ?? 'Administrator' }}</span>
          </div>
        </div>
      </div>
    </header>

    <div class="content">

      @if(session('success'))
        <div class="flash flash-ok"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="flash flash-err"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="flash flash-err"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
      @endif

      @yield('content')
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
