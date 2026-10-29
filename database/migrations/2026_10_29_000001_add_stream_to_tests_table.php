<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            if (!Schema::hasColumn('tests', 'stream')) {
                $table->string('stream', 10)->default('school')->after('type');
            }
            if (!Schema::hasColumn('tests', 'class_level')) {
                $table->unsignedTinyInteger('class_level')->nullable()->after('stream');
            }
            if (!Schema::hasColumn('tests', 'madarsa_section')) {
                $table->char('madarsa_section', 1)->nullable()->after('class_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn(['stream', 'class_level', 'madarsa_section']);
        });
    }
};
