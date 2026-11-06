<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeExpense;
use Illuminate\Http\Request;

class OfficeExpenseController extends Controller
{
    public function index(Request $request)
    {
        $month = trim((string) $request->query('month', now()->format('Y-m')));
        $category = trim((string) $request->query('category', ''));
        $q = trim((string) $request->query('q', ''));

        $base = OfficeExpense::query()
            ->when($month !== '', fn ($query) => $this->forMonth($query, $month))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';
                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(title) LIKE ?', [$like])
                        ->orWhereRaw("LOWER(COALESCE(paid_to, '')) LIKE ?", [$like])
                        ->orWhereRaw("LOWER(COALESCE(reference, '')) LIKE ?", [$like])
                        ->orWhereRaw("LOWER(COALESCE(note, '')) LIKE ?", [$like]);
                });
            });

        $expenses = (clone $base)->orderByDesc('expense_date')->orderByDesc('id')->get();

        $monthTotal = (int) OfficeExpense::query()
            ->when($month !== '', fn ($query) => $this->forMonth($query, $month))
            ->sum('amount');

        $todayTotal = (int) OfficeExpense::whereDate('expense_date', now()->toDateString())->sum('amount');
        $allTotal = (int) OfficeExpense::sum('amount');

        $byCategory = OfficeExpense::query()
            ->when($month !== '', fn ($query) => $this->forMonth($query, $month))
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return view('admin.expenses', [
            'expenses' => $expenses,
            'month' => $month,
            'category' => $category,
            'q' => $q,
            'monthLabel' => $month !== '' ? date('F Y', strtotime($month . '-01')) : 'All',
            'monthTotal' => $monthTotal,
            'todayTotal' => $todayTotal,
            'allTotal' => $allTotal,
            'byCategory' => $byCategory,
            'categories' => OfficeExpense::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = session('admin_id');

        OfficeExpense::create($data);

        return back()->with('success', 'Expense saved: ' . $data['title'] . ' — Rs. ' . number_format($data['amount']));
    }

    public function update(Request $request, int $id)
    {
        $expense = OfficeExpense::findOrFail($id);
        $expense->update($this->validated($request));

        return back()->with('success', 'Expense updated: ' . $expense->title);
    }

    public function destroy(int $id)
    {
        $expense = OfficeExpense::findOrFail($id);
        $title = $expense->title;
        $expense->delete();

        return back()->with('success', 'Expense deleted: ' . $title);
    }

    private function forMonth($query, string $month)
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $month, $m)) {
            return $query->whereYear('expense_date', (int) $m[1])->whereMonth('expense_date', (int) $m[2]);
        }

        return $query;
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'integer', 'min:1'],
            'expense_date' => ['required', 'date'],
            'paid_to' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['required', 'string', 'max:40'],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
        ]);

        $data['paid_to'] = (string) ($data['paid_to'] ?? '');
        $data['reference'] = (string) ($data['reference'] ?? '');
        $data['amount'] = (int) $data['amount'];

        return $data;
    }
}