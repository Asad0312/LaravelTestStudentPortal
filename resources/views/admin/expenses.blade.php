@extends('layouts.admin')

@section('page_title', 'Office Expenses')
@section('page_sub', 'Extra office kharchay — ' . $monthLabel . ' total Rs. ' . number_format($monthTotal))

@section('content')
<div class="row g-3 mb-3">
  <div class="col-6 col-md-4">
    <div class="card card-soft p-3 text-center">
      <div class="stat-value">Rs. {{ number_format($monthTotal) }}</div>
      <div class="stat-label">{{ $monthLabel }} Expenses</div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="card card-soft p-3 text-center">
      <div class="stat-value text-warning">Rs. {{ number_format($todayTotal) }}</div>
      <div class="stat-label">Today</div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card card-soft p-3 text-center">
      <div class="stat-value text-danger">Rs. {{ number_format($allTotal) }}</div>
      <div class="stat-label">All Time</div>
    </div>
  </div>
</div>

<div class="card card-soft p-3 mb-3">
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <form method="get" action="{{ route('admin.expenses.index') }}" class="d-flex flex-wrap gap-2 align-items-end" style="flex:1;min-width:280px;">
      <div>
        <label class="muted" style="font-size:12px;">Month</label>
        <input class="form-ctrl" type="month" name="month" value="{{ $month }}" />
      </div>
      <div>
        <label class="muted" style="font-size:12px;">Category</label>
        <select class="form-ctrl" name="category">
          <option value="">All</option>
          @foreach($categories as $c)
            <option value="{{ $c }}" {{ $category === $c ? 'selected' : '' }}>{{ $c }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="muted" style="font-size:12px;">Search</label>
        <input class="form-ctrl" type="text" name="q" value="{{ $q }}" placeholder="Title / paid to / reference" />
      </div>
      <button class="btn-primary-grad" style="padding:9px 16px;font-size:13px;" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i> Filter</button>
      @if($q !== '' || $category !== '')
        <a class="btn btn-light" style="padding:9px 14px;font-size:13px;" href="{{ route('admin.expenses.index', ['month' => $month]) }}">Clear</a>
      @endif
    </form>
    <button class="btn-grad" style="padding:9px 14px;font-size:13px;" data-bs-toggle="modal" data-bs-target="#addExpenseModal"><i class="fa-solid fa-plus me-1"></i> Add Expense</button>
  </div>
</div>

@if($byCategory->count())
<div class="card card-soft p-3 mb-3">
  <div class="section-head"><h3><i class="fa-solid fa-chart-pie me-1 text-primary"></i> {{ $monthLabel }} — Category Breakdown</h3></div>
  <div class="d-flex flex-wrap gap-2">
    @foreach($byCategory as $row)
      <span class="badge bg-soft-primary" style="font-size:12px;padding:7px 12px;">
        {{ $row->category }}: <b>Rs. {{ number_format($row->total) }}</b>
      </span>
    @endforeach
  </div>
</div>
@endif

<div class="card card-soft p-3">
  <div class="section-head"><h3><i class="fa-solid fa-receipt me-1"></i> Expense Records</h3></div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Date</th><th>Title</th><th>Category</th><th>Paid To</th><th>Method</th><th>Reference</th><th class="text-end">Amount</th><th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($expenses as $e)
          <tr>
            <td style="white-space:nowrap;">{{ $e->expense_date?->format('d M Y') }}</td>
            <td>
              <b style="font-size:13.5px;">{{ $e->title }}</b>
              @if($e->note)
                <div class="muted" style="font-size:11.5px;">{{ Str::limit($e->note, 60) }}</div>
              @endif
            </td>
            <td><span class="badge bg-light text-dark">{{ $e->category }}</span></td>
            <td>{{ $e->paid_to ?: '—' }}</td>
            <td>{{ $e->payment_method }}</td>
            <td class="muted">{{ $e->reference ?: '—' }}</td>
            <td class="text-end text-danger"><b>Rs. {{ number_format($e->amount) }}</b></td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editExpense{{ $e->id }}"><i class="fa-solid fa-pen"></i></button>
              <form action="{{ route('admin.expenses.destroy', $e->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                @csrf
                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center muted py-4">Is month / filter ka koi expense nahi mila.</td></tr>
        @endforelse
      </tbody>
      @if($expenses->count())
        <tfoot>
          <tr>
            <th colspan="6" class="text-end">Filtered Total</th>
            <th class="text-end text-danger">Rs. {{ number_format($expenses->sum('amount')) }}</th>
            <th></th>
          </tr>
        </tfoot>
      @endif
    </table>
  </div>
</div>

{{-- Add Expense Modal --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.expenses.store') }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Add Office Expense</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-8"><label class="muted" style="font-size:12px;">Expense Title *</label><input class="form-ctrl" name="title" required placeholder="e.g. Electricity bill, Chairs repair" /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Amount (Rs.) *</label><input class="form-ctrl" type="number" name="amount" min="1" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Category *</label>
            <select class="form-ctrl" name="category" required>
              @foreach($categories as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Date *</label><input class="form-ctrl" type="date" name="expense_date" value="{{ now()->toDateString() }}" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Payment Method *</label>
            <select class="form-ctrl" name="payment_method"><option>Cash</option><option>Bank Transfer</option><option>JazzCash</option><option>Easypaisa</option><option>Cheque</option></select>
          </div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Paid To</label><input class="form-ctrl" name="paid_to" placeholder="Vendor / person name" /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Reference / Voucher No.</label><input class="form-ctrl" name="reference" /></div>
          <div class="col-12"><label class="muted" style="font-size:12px;">Note</label><textarea class="form-ctrl" name="note" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-primary-grad" style="padding:8px 16px;">Save Expense</button>
      </div>
    </form>
  </div>
</div>

{{-- Edit Expense Modals --}}
@foreach($expenses as $e)
<div class="modal fade" id="editExpense{{ $e->id }}" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="{{ route('admin.expenses.update', $e->id) }}">
      @csrf
      <div class="modal-header"><h6 class="modal-title fw-bold">Edit Expense — {{ $e->title }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-8"><label class="muted" style="font-size:12px;">Expense Title *</label><input class="form-ctrl" name="title" value="{{ $e->title }}" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Amount (Rs.) *</label><input class="form-ctrl" type="number" name="amount" min="1" value="{{ $e->amount }}" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Category *</label>
            <select class="form-ctrl" name="category" required>
              @foreach($categories as $c)<option value="{{ $c }}" {{ $e->category === $c ? 'selected' : '' }}>{{ $c }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Date *</label><input class="form-ctrl" type="date" name="expense_date" value="{{ $e->expense_date?->format('Y-m-d') }}" required /></div>
          <div class="col-md-4"><label class="muted" style="font-size:12px;">Payment Method *</label>
            <select class="form-ctrl" name="payment_method">
              @foreach(['Cash','Bank Transfer','JazzCash','Easypaisa','Cheque'] as $m)
                <option {{ $e->payment_method === $m ? 'selected' : '' }}>{{ $m }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Paid To</label><input class="form-ctrl" name="paid_to" value="{{ $e->paid_to }}" /></div>
          <div class="col-md-6"><label class="muted" style="font-size:12px;">Reference / Voucher No.</label><input class="form-ctrl" name="reference" value="{{ $e->reference }}" /></div>
          <div class="col-12"><label class="muted" style="font-size:12px;">Note</label><textarea class="form-ctrl" name="note" rows="2">{{ $e->note }}</textarea></div>
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
@endsection
