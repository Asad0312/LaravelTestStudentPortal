<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamFee extends Model
{
    protected $fillable = [
        'exam_event_id', 'student_id', 'amount', 'status', 'paid_on', 'received_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'date',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(ExamEvent::class, 'exam_event_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
