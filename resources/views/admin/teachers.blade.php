@extends('layouts.admin')

@section('page_title', 'Teachers')
@section('page_sub', 'Teachers: ' . $teachers->count() . ' • Salary this month: Rs. ' . number_format($totalSalary))

@section('content')
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value">{{ $teachers->count() }}</div><div class="stat-label">Total Teachers</div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value">Rs. {{ number_format($totalSalary) }}</div><div class="stat-label">Monthly Salary</div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value text-success">Rs. {{ number_format($paidSalary) }}</div><div class="stat-label">Paid</div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-soft p-3 text-center"><div class="stat-value text-danger">Rs. {{ number_format($pendingSalary) }}</div><div class="stat-label">Pending</div></div>
  </div>
</div>

<div class="card card-soft p-3 mb-3">
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <form method="get" action="{{ route('admin.teachers.index') }}" class="d-flex flex-wrap gap-2" style="flex:1;min-width:280px;">
      <input class="form-ctrl" type="text" name="q" value="{{ $q }}" placeholder="Search by Teacher ID / Name / Designation / Phone" style="flex:1;min-width:240px;" />
      <button class="btn-primary-grad" style="padding:9px 16px;font-size:13px;" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i> Filter</button>
      @if($q !== '')
        <a class="btn btn-light" style="padding:9px 14px;font-size:13px;" href="{{ route('admin.teachers.index') }}">Clear</a>
      @endif
    </form>
    <button class="btn-grad" style="padding:9px 14px;font-size:13px;" data-bs-toggle="modal" data-bs-target="#addTeacherModal"><i class="fa-solid fa-plus me-1"></i> Add Teacher</button>
  </div>
</div>

<div class="card card-soft p-3 mb-3">
  <div class="section-head"><h3><i class="fa-solid fa-money-bill-wave me-1 text-success"></i> Salary Management ({{ now()->format('F Y') }})</h3></div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Teacher</th><th>ID</th><th>Designation</th><th>Contact</th><th>Salary</th><th>Paid</th><th>Remaining</th><th>Status</th><th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td>
              <b style="font-size:13.5px;">{{ $r['name'] }}</b>
              <div class="muted" style="font-size:11.5px;">{{ $r['gender'] }}{{ $r['subject'] ? ' • '.$r['subject'] : '' }}</div>
            </td>
            <td><span class="badge bg-soft-primary">{{ $r['teacherId'] }}</span></td>
            <td>{{ $r['designation'] }}</td>
            <td>{{ $r['phone'] }}</td>
            <td>Rs. {{ number_format($r['monthly']) }}</td>
            <td class="text-success">Rs. {{ number_format($r['paid']) }}</td>
            <td class="{{ $r['remaining'] > 0 ? 'text-danger' : '' }}">Rs. {{ number_format($r['remaining']) }}</td>
            <td><span class="badge {{ $r['payStatus'] === 'Paid' ? 'bg-soft-success' : ($r['payStatus'] === 'Pending' ? 'bg-soft-warning' : 'bg-soft-danger') }}">{{ $r['payStatus'] }}</span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-info" href="{{ route('admin.teachers.history', $r['dbId']) }}"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
              <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $r['dbId'] }}"><i class="fa-solid fa-plus"></i> Pay</button>
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="text-center muted py-4">No teachers found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="card card-soft p-3">
  <div class="section-head"><h3><i class="fa-solid fa-users me-1"></i> Teacher Directory</h3></div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr><th>Teacher</th><th>ID</th><th>Gender</th><th>Designation</th><th>Subject</th><th>Joining</th><th>Status</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
        @forelse($teachers as $t)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="https://api.dicebear.com/8.x/initials/svg?seed={{ urlencode($t->avatar_seed ?: $t->name) }}" style="width:34px;height:34px;border-radius:10px;">
                <div><b>{{ $t->name }}</b><div class="muted" style="font-size:11px;">{{ $t->whatsapp ?: $t->phone ?: '—' }}</div></div>
              </div>
            </td>
            <td><span class="badge bg-soft-primary">{{ $t->teacher_id }}</span></td>
            <td>{{ $t->gender }}</td>
            <td>{{ $t->designation }}</td>
            <td>{{ $t->subject ?: '—' }}</td>
            <td>{{ $t->joining_date ? $t->joining_date->format('d M Y') : '—' }}</td>
            <td><span class="badge {{ $t->status === 'Active' ? 'bg-soft-success' : 'bg-soft-danger' }}">{{ $t->status }}</span></td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTeacher{{ $t->id }}"><i class="fa-solid fa-pen"></i></button>
              <form action="{{ route('admin.teachers.destroy', $t->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this teacher?')">
                @csrf
                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center muted py-4">No teachers added yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Add Teacher Modal --}}
<div class="modal fade" id="addTeacherModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.teachers.store') }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Add Teacher</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Teacher ID</label><input class="form-ctrl" value="{{ $nextId }}" readonly /></div>
          <div class="col-md-8"><label class="muted" style="font-size:12px;">Full Name *</label><input class="form-ctrl" name="name" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Father Name</label><input class="form-ctrl" name="father_name" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Gender *</label>
            <select class="form-ctrl" name="gender" required>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">WhatsApp / Phone</label><input class="form-ctrl" name="phone" placeholder="03XXXXXXXXX ya +923XXXXXXXXX" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Email</label><input class="form-ctrl" name="email" type="email" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Designation *</label><input class="form-ctrl" name="designation" required placeholder="e.g. Senior Teacher" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Subject / Specialization</label><input class="form-ctrl" name="subject" placeholder="e.g. Math, Quran" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Qualification</label><input class="form-ctrl" name="qualification" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">CNIC</label><input class="form-ctrl" name="cnic" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Joining Date</label><input class="form-ctrl" name="joining_date" type="date" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Monthly Salary (Rs.) *</label><input class="form-ctrl" name="monthly_salary" type="number" min="0" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Status</label>
            <select class="form-ctrl" name="status"><option value="Active">Active</option><option value="Inactive">Inactive</option></select>
          </div>
          <div class="col-12"><label class="muted" style="font-size:12px;">Address</label><input class="form-ctrl" name="address" /></div>
          <div class="col-12"><label class="muted" style="font-size:12px;">Notes</label><textarea class="form-ctrl" name="notes" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:8px 16px;">Add Teacher</button>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modals --}}
@foreach($teachers as $t)
<div class="modal fade" id="editTeacher{{ $t->id }}" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.teachers.update', $t->id) }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Edit Teacher — {{ $t->name }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Teacher ID</label><input class="form-ctrl" value="{{ $t->teacher_id }}" readonly /></div>
          <div class="col-md-8"><label class="muted" style="font-size:12px;">Full Name *</label><input class="form-ctrl" name="name" value="{{ $t->name }}" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Father Name</label><input class="form-ctrl" name="father_name" value="{{ $t->father_name }}" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Gender *</label>
            <select class="form-ctrl" name="gender" required>
              @foreach(['Male','Female','Other'] as $g)<option value="{{ $g }}" {{ $t->gender === $g ? 'selected' : '' }}>{{ $g }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">WhatsApp / Phone</label><input class="form-ctrl" name="phone" value="{{ $t->whatsapp ?: $t->phone }}" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Email</label><input class="form-ctrl" name="email" type="email" value="{{ $t->email }}" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Designation *</label><input class="form-ctrl" name="designation" value="{{ $t->designation }}" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Subject</label><input class="form-ctrl" name="subject" value="{{ $t->subject }}" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Qualification</label><input class="form-ctrl" name="qualification" value="{{ $t->qualification }}" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">CNIC</label><input class="form-ctrl" name="cnic" value="{{ $t->cnic }}" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Joining Date</label><input class="form-ctrl" name="joining_date" type="date" value="{{ $t->joining_date ? $t->joining_date->format('Y-m-d') : '' }}" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Monthly Salary (Rs.) *</label><input class="form-ctrl" name="monthly_salary" type="number" min="0" value="{{ $t->monthly_salary }}" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Status</label>
            <select class="form-ctrl" name="status"><option value="Active" {{ $t->status === 'Active' ? 'selected' : '' }}>Active</option><option value="Inactive" {{ $t->status === 'Inactive' ? 'selected' : '' }}>Inactive</option></select>
          </div>
          <div class="col-12"><label class="muted" style="font-size:12px;">Address</label><input class="form-ctrl" name="address" value="{{ $t->address }}" /></div>
          <div class="col-12"><label class="muted" style="font-size:12px;">Notes</label><textarea class="form-ctrl" name="notes" rows="2">{{ $t->notes }}</textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:8px 16px;">Save Changes</button>
      </div>
    </form>
  </div>
</div>
@endforeach

{{-- Pay Salary Modals --}}
@foreach($rows as $r)
<div class="modal fade" id="payModal{{ $r['dbId'] }}" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <form class="modal-content" method="post" action="{{ route('admin.teachers.pay') }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Pay Salary — {{ $r['name'] }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="teacher_id" value="{{ $r['dbId'] }}" />
        <div class="mb-2"><label class="muted" style="font-size:12px;">Teacher ID</label><input class="form-ctrl" value="{{ $r['teacherId'] }}" readonly /></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Month *</label><input class="form-ctrl" type="month" name="month" value="{{ $r['monthRaw'] }}" required /></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Monthly Salary *</label><input class="form-ctrl" type="number" name="monthly_salary" min="0" value="{{ $r['monthly'] }}" required /></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Amount Paid *</label><input class="form-ctrl" type="number" name="amount" min="1" value="{{ max(1, $r['remaining']) }}" required /></div>
        <div class="mb-2"><label class="muted" style="font-size:12px;">Method *</label>
          <select class="form-ctrl" name="method"><option>Cash</option><option>Bank Transfer</option><option>JazzCash</option><option>Easypaisa</option></select>
        </div>
        <div><label class="muted" style="font-size:12px;">Note</label><input class="form-ctrl" name="note" /></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:8px 16px;">Save</button>
      </div>
    </form>
  </div>
</div>
@endforeach

@if($recent->count())
<div class="card card-soft p-3 mt-3">
  <div class="section-head"><h3>Recent Salary Payments</h3></div>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead><tr><th>Teacher</th><th>Month</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
      <tbody>
        @foreach($recent as $p)
          <tr>
            <td>{{ $p->teacher?->name ?? '—' }}</td>
            <td>{{ date('F Y', strtotime($p->month.'-01')) }}</td>
            <td class="text-success">Rs. {{ number_format($p->amount) }}</td>
            <td>{{ $p->method }}</td>
            <td class="muted">{{ $p->paid_on?->format('d M Y') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
