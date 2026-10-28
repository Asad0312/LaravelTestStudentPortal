@extends('layouts.admin')

@section('page_title', 'Books')
@section('page_sub', 'School & Madarsa books with prices')

@section('content')
<div class="card card-soft p-3 mb-3">
  <form method="get" action="{{ route('admin.books.index') }}" class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="muted" style="font-size:12px;">Type</label>
      <select class="form-ctrl" name="stream" onchange="toggle()">
        <option value="school" {{ $stream==='school'?'selected':'' }}>School</option>
        <option value="madarsa" {{ $stream==='madarsa'?'selected':'' }}>Madarsa</option>
      </select>
    </div>
    <div class="col-md-3" id="cwrap">
      <label class="muted" style="font-size:12px;">Class</label>
      <select class="form-ctrl" name="class_level">
        @for($i=1;$i<=8;$i++)<option value="{{ $i }}" {{ ($classLevel==$i)?'selected':'' }}>Class {{ $i }}</option>@endfor
      </select>
    </div>
    <div class="col-md-3" id="mwrap" style="display:none;">
      <label class="muted" style="font-size:12px;">Section</label>
      <select class="form-ctrl" name="section">
        @foreach(['A','B','C','D'] as $s)<option {{ ($section===$s)?'selected':'' }}>{{ $s }}</option>@endforeach
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn-primary-grad" style="padding:9px 16px;"><i class="fa-solid fa-filter me-1"></i> Load</button>
      <button type="button" class="btn-grad" style="padding:9px 14px;" onclick="autofill()"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Fill</button>
    </div>
  </form>
</div>

<form method="post" action="{{ route('admin.books.save') }}">
  @csrf
  <input type="hidden" name="stream" value="{{ $stream }}">
  <input type="hidden" name="class_level" value="{{ $classLevel }}">
  <input type="hidden" name="madarsa_section" value="{{ $section }}">
  <div class="card card-soft p-3">
    <div class="d-flex justify-content-between mb-2">
      <h6 class="fw-bold mb-0">{{ $stream === 'school' ? 'School Books — Class '.$classLevel : 'Madarsa Books — Section '.$section }}</h6>
      <button class="btn-primary-grad" style="padding:8px 14px;">Save All</button>
    </div>
    <div class="table-responsive">
      <table class="table align-middle" id="bookTable">
        <thead><tr><th>Book Title</th><th style="width:180px;">Price (Rs.)</th><th style="width:80px;"></th></tr></thead>
        <tbody>
          @forelse($books as $b)
            <tr>
              <td><input class="form-ctrl" name="items[{{ $loop->index }}][title]" value="{{ $b->title }}"></td>
              <td><input class="form-ctrl" type="number" step="0.01" name="items[{{ $loop->index }}][price]" value="{{ $b->price }}"></td>
              <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i></button></td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center muted">Koi books nahi — Auto-Fill se add karein ya manually add karein.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <button type="button" class="btn-grad" onclick="addRow()"><i class="fa-solid fa-plus me-1"></i> Add Row</button>
  </div>
</form>

<script>
let idx = {{ $books->count() }};
function toggle(){
  const s = document.querySelector('select[name=stream]').value;
  document.getElementById('cwrap').style.display = s==='school'?'':'none';
  document.getElementById('mwrap').style.display = s==='madarsa'?'':'none';
}
toggle();
function addRow(title=''){
  const tbody = document.querySelector('#bookTable tbody');
  if(tbody.querySelector('.text-center')) tbody.innerHTML='';
  const tr = document.createElement('tr');
  tr.innerHTML = '<td><input class="form-ctrl" name="items['+idx+'][title]" value="'+title+'"></td><td><input class="form-ctrl" type="number" step="0.01" name="items['+idx+'][price]" value="0"></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()"><i class="fa-solid fa-trash"></i></button></td>';
  tbody.appendChild(tr); idx++;
}
function autofill(){
  const s = document.querySelector('select[name=stream]').value;
  fetch('{{ route('admin.books.templates') }}').then(r=>r.json()).then(data=>{
    const tbody = document.querySelector('#bookTable tbody');
    tbody.innerHTML='';
    idx=0;
    if(s==='school'){
      const c = document.querySelector('select[name=class_level]').value;
      data.forEach(x=>{ if(x.stream==='school' && x.class_level==c){ addRow(x.title); }});
    }else{
      const sec = document.querySelector('select[name=section]').value;
      data.forEach(x=>{ if(x.stream==='madarsa' && x.madarsa_section===sec){ addRow(x.title); }});
    }
  });
}
</script>
@endsection
