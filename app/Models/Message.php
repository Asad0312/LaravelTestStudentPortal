<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['title', 'body', 'class_no', 'section', 'priority', 'expires_at', 'admin_id'];

    protected $casts = [
        'expires_at' => 'date',
    ];

    /** Saamo per expired (expiry date guzar gayi) — portal par nahi dikhta. */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lt(now()->startOfDay());
    }

    /** Us student ke liye jo messages visible hain (sab classes + apni class/section, expired hata ke). */
    public function scopeForStudent(Builder $query, Student $student): Builder
    {
        return $query->where(function (Builder $q) use ($student) {
            $q->whereNull('class_no')
                ->orWhere(function (Builder $q2) use ($student) {
                    $q2->where('class_no', $student->class_no)
                        ->where(function (Builder $q3) use ($student) {
                            $q3->where('section', '')->orWhere('section', $student->section);
                        });
                });
        })->where(function (Builder $q) {
            $q->whereNull('expires_at')
                ->orWhereDate('expires_at', '>=', now()->toDateString());
        });
    }

    /** Sirf zinda (non-expired) messages. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expires_at')
                ->orWhereDate('expires_at', '>=', now()->toDateString());
        });
    }

    /** Aadmi ke liye readable target label. */
    public function targetLabel(): string
    {
        if ($this->class_no === null) {
            return 'All Students';
        }

        return 'Class ' . $this->class_no . ($this->section ? '-' . $this->section : ' (All sections)');
    }
}
