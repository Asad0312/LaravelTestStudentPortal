<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'whatsapp')) {
                $table->string('whatsapp', 40)->default('')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('students', 'stream')) {
                $table->enum('stream', ['school', 'madarsa', 'both'])->default('school')->after('address');
            }
            if (!Schema::hasColumn('students', 'class_level')) {
                $table->unsignedTinyInteger('class_level')->nullable()->after('stream');
            }
            if (!Schema::hasColumn('students', 'madarsa_section')) {
                $table->char('madarsa_section', 1)->nullable()->after('class_level');
            }
            if (!Schema::hasColumn('students', 'monthly_fee')) {
                $table->decimal('monthly_fee', 10, 2)->default(2500)->after('password');
            }
            if (!Schema::hasColumn('students', 'notifications_enabled')) {
                $table->boolean('notifications_enabled')->default(true)->after('monthly_fee');
            }
            if (!Schema::hasColumn('students', 'notes')) {
                $table->text('notes')->nullable()->after('notifications_enabled');
            }
            if (!Schema::hasColumn('students', 'deleted_at')) {
                $table->softDeletes();
            }
            $table->index(['stream', 'class_level', 'madarsa_section'], 'students_stream_idx');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_stream_idx');
            $table->dropSoftDeletes();
            $table->dropColumn(['whatsapp', 'stream', 'class_level', 'madarsa_section', 'monthly_fee', 'notifications_enabled', 'notes']);
        });
    }
};