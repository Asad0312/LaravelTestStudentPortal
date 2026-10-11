@extends('layouts.admin')

@section('page_title', 'Messages')
@section('page_sub', 'Admin yahan message likhe — students ke portal par foran show hoga')

@section('content')
<div class="card card-soft p-3 mb-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
  <span class="muted" style="font-size:13px;">
    <i class="fa-solid fa-bullhorn text-primary"></i>
    Message likhein — <b>sab students</b> ko ya <b>ek class/section</b> ko bhejein. Portal par ye "Admin Messages" section me dikh kar auto-refresh bhi ho jata hai.
  </span>
  <button class="btn-primary-grad" style="padding:9px 16px;font-size:13px;" data-bs-toggle="modal" data-bs-target="#addMsgModal">
    <i class="fa-solid fa-plus me-1"></i> New Message
  </button>
</div>

<div class="card card-soft p-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="section-title mb-0">Sent Messages</h5>
    <span class="badge bg-soft-info" style="font-size:11.5px;">{{ $total }} total</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Date</th><th>Message</th><th>Target</th><th>Priority</th><th>Expiry</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        @forelse($rows as $m)
          <tr>
            <td class="muted" style="font-size:12px;">{{ $m->created_at?->format('d M Y') }}<div style="font-size:11px;">{{ $m->created_at?->format('h:i A') }}</div></td>
            <td style="max-width:420px;">
              @if($m->title)<b style="font-size:13.5px;">{{ $m->title }}</b><br />@endif
              <span style="font-size:13px;">{{ \Illuminate\Support\Str::limit($m->body, 140) }}</span>
            </td>
            <td><span class="badge bg-soft-primary" style="font-size:11.5px;">{{ $m->targetLabel() }}</span></td>
            <td>
              <span class="obj-badge {{ $m->priority === 'Important' ? 'month' : 'week' }}">{{ $m->priority }}</span>
            </td>
            <td>
              @if($m->isExpired())
                <span class="badge bg-soft-danger" style="font-size:11.5px;">Expired {{ $m->expires_at->format('d M Y') }}</span>
              @elseif($m->expires_at)
                <span class="badge bg-soft-warning" style="font-size:11.5px;">Till {{ $m->expires_at->format('d M Y') }}</span>
              @else
                <span class="muted" style="font-size:11.5px;">No expiry</span>
              @endif
            </td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMsg{{ $m->id }}"><i class="fa-solid fa-pen"></i></button>
              <form method="post" action="{{ route('admin.messages.destroy', $m->id) }}" class="d-inline" onsubmit="return confirm('Message delete karein?')">
                @csrf
                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center muted py-4">Koi message nahi bheja — "New Message" se pehla message likhein.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ===== ADD MESSAGE ===== --}}
<div class="modal fade" id="addMsgModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.messages.store') }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">New Message</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12"><label class="muted" style="font-size:12px;">Title (optional)</label><input class="form-ctrl" name="title" placeholder="e.g. Fee Reminder / Holiday Notice" /></div>
          <div class="col-12"><label class="muted" style="font-size:12px;">Message *</label><textarea class="form-ctrl" name="body" rows="4" required placeholder="Message yahan likhein..."></textarea></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Send to *</label>
            <select class="form-ctrl js-target" name="target">
              <option value="all">All Students</option>
              <option value="class">Specific Class</option>
            </select>
          </div>
          <div class="col-md-4 js-class-part d-none"><label class="muted" style="font-size:12px;">Class</label>
            <select class="form-ctrl" name="class_no">@for($i = 1; $i <= 12; $i++)<option value="{{ $i }}" {{ $i === 6 ? 'selected' : '' }}>Class {{ $i }}</option>@endfor</select>
          </div>
          <div class="col-md-4 js-class-part d-none"><label class="muted" style="font-size:12px;">Section</label>
            <select class="form-ctrl" name="section"><option value="">All Sections</option>@foreach(['A','B','C','D'] as $sec)<option>{{ $sec }}</option>@endforeach</select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Priority</label>
            <select class="form-ctrl" name="priority"><option>Normal</option><option>Important</option></select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Expiry Date (optional)</label>
            <input class="form-ctrl" type="date" name="expires_at" min="{{ now()->toDateString() }}" />
            <div class="muted" style="font-size:11px;">Is date ke baad message portal par khud hide ho jayega.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;">Send Message</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== EDIT MESSAGES ===== --}}
@foreach($rows as $m)
<div class="modal fade" id="editMsg{{ $m->id }}" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.messages.update', $m->id) }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Edit Message</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12"><label class="muted" style="font-size:12px;">Title (optional)</label><input class="form-ctrl" name="title" value="{{ $m->title }}" /></div>
          <div class="col-12"><label class="muted" style="font-size:12px;">Message *</label><textarea class="form-ctrl" name="body" rows="4" required>{{ $m->body }}</textarea></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Send to *</label>
            <select class="form-ctrl js-target" name="target">
              <option value="all" {{ $m->class_no === null ? 'selected' : '' }}>All Students</option>
              <option value="class" {{ $m->class_no !== null ? 'selected' : '' }}>Specific Class</option>
            </select>
          </div>
          <div class="col-md-4 js-class-part {{ $m->class_no === null ? 'd-none' : '' }}"><label class="muted" style="font-size:12px;">Class</label>
            <select class="form-ctrl" name="class_no">@for($i = 1; $i <= 12; $i++)<option value="{{ $i }}" {{ (int) $m->class_no === $i ? 'selected' : '' }}>Class {{ $i }}</option>@endfor</select>
          </div>
          <div class="col-md-4 js-class-part {{ $m->class_no === null ? 'd-none' : '' }}"><label class="muted" style="font-size:12px;">Section</label>
            <select class="form-ctrl" name="section"><option value="" {{ $m->section === '' ? 'selected' : '' }}>All Sections</option>@foreach(['A','B','C','D'] as $sec)<option {{ $m->section === $sec ? 'selected' : '' }}>{{ $sec }}</option>@endforeach</select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Priority</label>
            <select class="form-ctrl" name="priority"><option {{ $m->priority === 'Normal' ? 'selected' : '' }}>Normal</option><option {{ $m->priority === 'Important' ? 'selected' : '' }}>Important</option></select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Expiry Date (optional)</label>
            <input class="form-ctrl" type="date" name="expires_at" value="{{ $m->expires_at?->format('Y-m-d') }}" min="{{ now()->toDateString() }}" />
            <div class="muted" style="font-size:11px;">Kap gaye par portal par khud hide ho jayega.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:9px 18px;">Update Message</button>
      </div>
    </form>
  </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.js-target').forEach(function (sel) {
    sel.addEventListener('change', function () {
      var form = sel.closest('form');
      form.querySelectorAll('.js-class-part').forEach(function (el) {
        el.classList.toggle('d-none', sel.value !== 'class');
      });
    });
  });
</script>
@endpush
