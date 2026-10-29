<?php

namespace App\Models;

use App\Support\Sql;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    protected $fillable = [
        'test_code', 'type', 'stream', 'class_level', 'madarsa_section', 'class_no', 'section', 'subject',
        'test_date', 'test_time', 'total_marks',
    ];

    protected function casts(): array
    {
        return [
            'test_date' => 'date',
            'class_level' => 'integer',
            'class_no' => 'integer',
            'total_marks' => 'integer',
        ];
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'test_id');
    }

    public static function nextTestCode(): string
    {
        $max = (int) static::selectRaw(
            'MAX(' . Sql::numericSuffix('test_code') . ') as m'
        )->value('m');

        return 'T-' . (max($max, 100) + 1);
    }
}
