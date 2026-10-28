<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamEvent extends Model
{
    protected $fillable = [
        'title', 'exam_month', 'stream', 'class_level', 'madarsa_section', 'exam_fee', 'created_by'
    ];

    protected $casts = [
        'exam_month' => 'date',
        'exam_fee' => 'decimal:2',
        'class_level' => 'integer',
    ];

    public function fees(): HasMany
    {
        return $this->hasMany(ExamFee::class);
    }
}
