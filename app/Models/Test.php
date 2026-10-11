<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    protected $fillable = [
        'test_code', 'type', 'class_no', 'section', 'subject',
        'test_date', 'test_time', 'total_marks',
    ];

    protected function casts(): array
    {
        return ['test_date' => 'date'];
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'test_id');
    }

    public static function nextTestCode(): string
    {
        $max = (int) static::selectRaw(
            "MAX(CAST(SUBSTRING_INDEX(test_code, '-', -1) AS UNSIGNED)) as m"
        )->value('m');

        return 'T-' . (max($max, 100) + 1);
    }
}
