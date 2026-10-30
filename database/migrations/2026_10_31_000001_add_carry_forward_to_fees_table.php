<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            // Amount carried into this month from previous unpaid months.
            $table->unsignedInteger('carried_amount')->default(0);
            // Oldest month whose balance was carried into this row (YYYY-MM).
            $table->char('carried_from', 7)->nullable();
            // Amount of this month's balance that was moved to the next month.
            $table->unsignedInteger('carried_out')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn(['carried_amount', 'carried_from', 'carried_out']);
        });
    }
};
