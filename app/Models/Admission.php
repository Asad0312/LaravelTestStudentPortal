<?php

namespace App\Models;

use App\Support\Sql;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    protected $fillable = [
        'app_id', 'name', 'father_name', 'phone', 'email', 'address',
        'class_no', 'section', 'student_id', 'password', 'status',
    ];

    protected $hidden = ['password'];

    public static function nextAppId(): string
    {
        $count  = static::count();
        $max    = (int) static::selectRaw(
            'MAX(' . Sql::numericSuffix('app_id') . ') as m'
        )->value('m');

        return 'APP-' . date('Y') . '-' . str_pad((string) (max($count, $max) + 1), 3, '0', STR_PAD_LEFT);
    }
}
