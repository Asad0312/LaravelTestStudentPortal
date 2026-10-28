<?php

namespace App\Models;

use App\Support\Sql;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{

    protected $fillable = [
        'student_id', 'name', 'father_name', 'phone', 'whatsapp', 'email', 'address',
        'stream', 'class_level', 'madarsa_section',
        'class_no', 'section', 'batch', 'admission_date', 'status',
        'password', 'avatar_seed', 'monthly_fee', 'notifications_enabled', 'notes',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
            'monthly_fee' => 'decimal:2',
            'notifications_enabled' => 'boolean',
            'class_level' => 'integer',
        ];
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'student_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'student_id');
    }

    /** Default portal password rule. */
    public static function defaultPassword(string $name): string
    {
        return strtolower(preg_replace('/\s+/', '', $name)) . '123';
    }

    public function getDisplayClassAttribute(): string
    {
        if (($this->stream ?? 'school') === 'madarsa') {
            return 'Madarsa Section ' . ($this->madarsa_section ?? $this->section);
        }
        if (($this->stream ?? 'school') === 'both') {
            $cls = $this->class_level ?? $this->class_no;
            $sec = $this->madarsa_section ?? $this->section;
            return 'Class ' . $cls . ' + Madarsa ' . $sec;
        }
        $cls = $this->class_level ?? $this->class_no;
        return 'Class ' . $cls;
    }

    public function getWhatsappNumberAttribute(): string
    {
        $num = $this->whatsapp ?: $this->phone;
        return self::normalizePhone($num);
    }

    public static function normalizePhone(string $num): string
    {
        $num = preg_replace('/\D+/', '', $num);
        if (strlen($num) === 11 && str_starts_with($num, '03')) {
            return '92' . substr($num, 1);
        }
        if (str_starts_with($num, '92')) {
            return $num;
        }
        if (str_starts_with($num, '0')) {
            return '92' . substr($num, 1);
        }
        return $num;
    }

    /** Next auto ID: STU-1 style. */
    public static function nextStudentId(): string
    {
        $max = (int) static::withTrashed()
            ->selectRaw('MAX(' . Sql::numericSuffix('student_id') . ') as m')
            ->value('m');
        if ($max < 1) {
            $max = 0;
        }

        return 'STU-' . ($max + 1);
    }

    public static function defaultFeeForClass(int $classNo): int
    {
        if ($classNo <= 2) {
            return 2000;
        }

        return $classNo <= 6 ? 2500 : 3000;
    }
}
