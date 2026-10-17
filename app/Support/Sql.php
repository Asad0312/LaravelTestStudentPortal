<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Sql
{
    /**
     * Portable SQL expression: trailing number of a code like "STU-1001"
     * (or "APP-2026-001") cast to an integer. Works on MySQL and PostgreSQL.
     */
    public static function numericSuffix(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql'  => "CAST(NULLIF(regexp_replace({$column}, '^.*[^0-9]', ''), '') AS INTEGER)",
            'sqlite' => "CAST({$column} AS INTEGER)",
            default  => "CAST(SUBSTRING_INDEX({$column}, '-', -1) AS UNSIGNED)",
        };
    }
}
