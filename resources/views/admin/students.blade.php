@extends('layouts.admin')

@section('page_title', 'Students')
@section('page_sub', $students->count() . ' students — search, add, edit')

@section('content')
<div class="card card-soft p-3 mb-3">
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <form method="get" action="{{ url('admin/students') }}" class="d-flex gap-2" style="min-width:280px;flex:1;">
      <input class="form-ctrl" type="text" name="q" value="{{ $q }}" placeholder="Search — name, STU-1001, class..." style="flex:1;" />
      <button class="btn-grad" style="padding:9px 16px;font-size:13px;" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <div class="d-flex gap-2">
      <a class="btn-grad" style="padding:9px 14px;font-size:13px;" href="{{ url('admin/export/students') }}"><i class="fa-solid fa-file-csv me-1"></i> CSV</a>
      <button class="btn-primary-grad" style="padding:9px 16px;font-size:13px;" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa-solid fa-user-plus me-1"></i> Add Student
      </button>
    </div>
  </div>
</div>

<div class="card card-soft p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Student</th><th>ID</th><th>Class/Section</th><th>Phone</th><th>Status</th><th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($students as $s)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img class="avatar" style="width:34px;height:34px;" src="https://api.dicebear.com/8.x/initials/svg?seed={{ urlencode($s->avatar_seed ?: $s->name) }}" alt="" />
                <div>
                  <b style="font-size:13.5px;">{{ $s->name }}</b>
                  @if($s->father_name)<div class="muted" style="font-size:11.5px;">S/o {{ $s->father_name }}</div>@endif
                </div>
              </div>
            </td>
            <td><span class="badge bg-soft-primary">{{ $s->student_id }}</span></td>
            <td>{{ method_exists($s, 'getDisplayClassAttribute') ? $s->display_class : 'Class '.$s->class_no.'-'.$s->section }}</td>
            <td>{{ $s->phone ?: '—' }}</td>
            <td>
              <span class="badge {{ $s->status === 'Active' ? 'bg-soft-success' : 'bg-soft-danger' }}">{{ $s->status }}</span>
            </td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $s->id }}"><i class="fa-solid fa-pen"></i></button>
              <form method="post" action="{{ route('admin.students.destroy', $s->id) }}" class="d-inline" onsubmit="return confirm('Ye student delete karein? Saara uska data bhi delete hoga.')">
                @csrf
                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center muted py-4">Koi student nahi — upar "Add Student" se pehla student add karein.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ===== ADD MODAL ===== --}}
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.students.store') }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Add New Student</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Full Name *</label><input class="form-ctrl" name="name" required /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Father Name</label><input class="form-ctrl" name="father_name" /></div>
          <div class="col-md-4">
            <label class="muted" style="font-size:12px;">Type *</label>
            <select class="form-ctrl" name="stream" id="addStream" required>
              <option value="school" selected>School</option>
              <option value="madarsa">Madarsa</option>
              <option value="both">School + Madarsa</option>
            </select>
          </div>
          <div class="col-md-4" id="addClassWrap">
            <label class="muted" style="font-size:12px;">Class *</label>
            <select class="form-ctrl" name="class_no">
              <option value="" disabled selected>Select Class</option>
              @for($i = 1; $i <= 8; $i++)<option value="{{ $i }}">Class {{ $i }}</option>@endfor
            </select>
          </div>
          <div class="col-md-4" id="addMadarsaWrap" style="display:none;">
            <label class="muted" style="font-size:12px;">Madarsa Section *</label>
            <select class="form-ctrl" name="madarsa_section">@foreach(['A','B','C','D'] as $sec)<option>{{ $sec }}</option>@endforeach</select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">WhatsApp / Phone</label><input class="form-ctrl" name="phone" placeholder="03XXXXXXXXX ya +923XXXXXXXXX" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Monthly Fee *</label><input class="form-ctrl" type="number" name="monthly_fee" value="2500" required /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Email (alerts ke liye)</label><input class="form-ctrl" type="email" name="email" /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Address</label><input class="form-ctrl" name="address" /></div>
          <div class="col-12"><label class="muted" style="font-size:12px;">Password (khaali chhoda to apne aap "name123" banega)</label><input class="form-ctrl" name="password" placeholder="auto generate" /></div>
        </div>
        <div class="muted mt-2" style="font-size:12px;"><i class="fa-solid fa-circle-info"></i> Next Student ID: <b>{{ $nextId }}</b> — save hone par automatically lag jayega.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;">Save Student</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('addStream')?.addEventListener('change', function(){
  const v = this.value;
  const cw = document.getElementById('addClassWrap');
  const mw = document.getElementById('addMadarsaWrap');
  if(cw) cw.style.display = (v === 'madarsa') ? 'none' : '';
  if(mw) mw.style.display = (v === 'school') ? 'none' : '';
});
</script>

{{-- ===== EDIT MODALS ===== --}}
@foreach($students as $s)
<div class="modal fade" id="editModal{{ $s->id }}" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.students.update', $s->id) }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Edit — {{ $s->name }} ({{ $s->student_id }})</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Full Name *</label><input class="form-ctrl" name="name" value="{{ old('name', $s->name) }}" required /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Father Name</label><input class="form-ctrl" name="father_name" value="{{ $s->father_name }}" /></div>
          <div class="col-md-3"><label class="muted" style="font-size:12px;">Class *</label>
            <select class="form-ctrl" name="class_no">@for($i = 1; $i <= 12; $i++)<option value="{{ $i }}" {{ $s->class_no === $i ? 'selected' : '' }}>Class {{ $i }}</option>@endfor</select>
          </div>
          <div class="col-md-3"><label class="muted" style="font-size:12px;">Section *</label>
            <select class="form-ctrl" name="section">@foreach(['A','B','C','D'] as $sec)<option {{ $s->section === $sec ? 'selected' : '' }}>{{ $sec }}</option>@endforeach</select>
          </div>
          <div class="col-md-3"><label class="muted" style="font-size:12px;">Status</label>
            <select class="form-ctrl" name="status"><option {{ $s->status === 'Active' ? 'selected' : '' }}>Active</option><option {{ $s->status === 'Inactive' ? 'selected' : '' }}>Inactive</option></select>
          </div>
          <div class="col-md-3"><label class="muted" style="font-size:12px;">Phone</label><input class="form-ctrl" name="phone" value="{{ $s->phone }}" /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Email</label><input class="form-ctrl" type="email" name="email" value="{{ $s->email }}" /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Address</label><input class="form-ctrl" name="address" value="{{ $s->address }}" /></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;">Update</button>
      </div>
    </form>
  </div>
</div>
@endforeach
@endsection
