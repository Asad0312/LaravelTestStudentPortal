<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>@yield('title', 'Student Portal')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
<style>body { background: #f1f5fb; }</style>
@stack('head')
</head>
<body>

<div class="portal-top anim-fade-up">
  <div class="brand-badge" style="font-size:16px;color:var(--primary-dark);">
    <div class="brand-logo" style="width:38px;height:38px;font-size:16px;"><i class="fa-solid fa-graduation-cap"></i></div>
    <b>StudentHub</b> <span class="badge bg-soft-primary" style="font-size:11px;">Student Portal</span>
  </div>
  <div class="ms-auto d-flex align-items-center gap-3">
    <div class="avatar-chip">
      <img class="avatar" src="https://api.dicebear.com/8.x/initials/svg?seed={{ urlencode($student->avatar_seed ?: $student->name) }}" alt="" />
      <div class="d-none d-md-block" style="text-align:right;">
        <b style="font-size:13.5px;">{{ $student->name }}</b><br />
        <span class="muted" style="font-size:11.5px;">{{ $student->student_id }}</span>
      </div>
    </div>
    <form method="post" action="{{ route('logout') }}" class="m-0">
      @csrf
      <button type="submit" class="logout-btn" title="Logout" style="border:0;"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
    </form>
  </div>
</div>

@yield('content')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
