<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeExpense extends Model
{
    protected $fillable = [
        'title', 'category', 'amount', 'expense_date', 'paid_to',
        'payment_method', 'reference', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'expense_date' => 'date',
        ];
    }

    public const CATEGORIES = [
        'Rent', 'Utilities', 'Stationery', 'Repair & Maintenance',
        'Transport', 'Cleaning', 'Furniture', 'Marketing', 'Misc',
    ];
}