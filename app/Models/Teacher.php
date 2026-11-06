<?php

namespace App\Models;

use App\Support\Sql;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'teacher_id', 'name', 'father_name', 'gender', 'phone', 'whatsapp', 'email', 'address',
        'designation', 'subject', 'qualification', 'cnic', 'joining_date', 'monthly_salary',
        'status', 'notes', 'avatar_seed',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'monthly_salary' => 'integer',
        ];
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(TeacherSalary::class, 'teacher_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TeacherPayment::class, 'teacher_id');
    }

    public static function nextTeacherId(): string
    {
        $max = (int) static::query()
            ->selectRaw('MAX(' . Sql::numericSuffix('teacher_id') . ') as m')
            ->value('m');

        return 'TCH-' . ($max + 1);
    }
}