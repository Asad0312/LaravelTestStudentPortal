<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'student_id', 'name', 'father_name', 'phone', 'email', 'address',
        'class_no', 'section', 'batch', 'admission_date', 'status',
        'password', 'avatar_seed',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['admission_date' => 'date'];
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

    /** Default portal password rule (same as old prototype). */
    public static function defaultPassword(string $name): string
    {
        return strtolower(preg_replace('/\s+/', '', $name)) . '123';
    }

    /** Next auto ID: STU-1001 style. */
    public static function nextStudentId(): string
    {
        $max = (int) static::query()
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(student_id, '-', -1) AS UNSIGNED)) as m")
            ->value('m');
        if ($max < 1000) {
            $max = 1000;
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
