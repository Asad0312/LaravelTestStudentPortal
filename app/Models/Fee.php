<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fee extends Model
{
    protected $fillable = [
        'student_id',
        'month',
        'monthly_amount',
        'carried_amount',
        'carried_from',
        'carried_out',
        'paid_amount',
    ];

    protected function casts(): array
    {
        return [
            'monthly_amount' => 'integer',
            'carried_amount' => 'integer',
            'carried_out' => 'integer',
            'paid_amount' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /** This month's own fee (total minus anything carried in). */
    public function getBaseAmountAttribute(): int
    {
        return max(0, (int) $this->monthly_amount - (int) $this->carried_amount);
    }

    /** What is still collectible for this month (excludes already carried-out balance). */
    public function getOutstandingAttribute(): int
    {
        return max(0, (int) $this->monthly_amount - (int) $this->paid_amount - (int) $this->carried_out);
    }
}
