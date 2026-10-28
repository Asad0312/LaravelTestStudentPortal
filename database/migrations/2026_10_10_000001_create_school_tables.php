<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->default('Admin');
            $table->string('email', 190)->unique();
            $table->string('password');
            $table->string('role', 60)->default('Headmaster');
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('class_no');
            $table->string('section', 8);
            $table->string('teacher_name', 120)->default('');
            $table->timestamps();
            $table->unique(['class_no', 'section']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 20)->unique();
            $table->string('name', 150);
            $table->string('father_name', 150)->default('');
            $table->string('phone', 40)->default('');
            $table->string('whatsapp', 40)->default('')->nullable();
            $table->string('email', 190)->default('');
            $table->string('address', 255)->default('');
            $table->enum('stream', ['school', 'madarsa', 'both'])->default('school');
            $table->unsignedTinyInteger('class_level')->nullable();
            $table->char('madarsa_section', 1)->nullable();
            $table->unsignedTinyInteger('class_no')->default(6);
            $table->string('section', 8)->default('A');
            $table->string('batch', 40)->default('Session 2026');
            $table->date('admission_date');
            $table->string('status', 20)->default('Active');
            $table->string('password');
            $table->decimal('monthly_fee', 10, 2)->default(2500);
            $table->boolean('notifications_enabled')->default(true);
            $table->text('notes')->nullable();
            $table->string('avatar_seed', 150)->default('');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['stream', 'class_level', 'madarsa_section']);
            $table->index(['class_no', 'section']);
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['Present', 'Absent'])->default('Present');
            $table->time('check_in_at')->nullable();
            $table->string('source', 20)->default('qr');
            $table->timestamps();
            $table->unique(['student_id', 'date']);
            $table->index('date');
        });

        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->char('month', 7);
            $table->unsignedInteger('monthly_amount')->default(2500);
            $table->unsignedInteger('paid_amount')->default(0);
            $table->timestamps();
            $table->unique(['student_id', 'month']);
            $table->index('month');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->char('month', 7);
            $table->unsignedInteger('amount');
            $table->date('paid_on');
            $table->string('method', 40)->default('Cash');
            $table->unsignedInteger('fine_amount')->default(0);
            $table->unsignedBigInteger('received_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('student_id');
        });

        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_code', 20)->unique();
            $table->enum('type', ['Weekly', 'Monthly'])->default('Weekly');
            $table->unsignedTinyInteger('class_no');
            $table->string('section', 8);
            $table->string('subject', 120);
            $table->date('test_date');
            $table->string('test_time', 20)->default('10:00 AM');
            $table->unsignedInteger('total_marks')->default(50);
            $table->timestamps();
            $table->index(['class_no', 'section']);
        });

        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedInteger('obtained')->default(0);
            $table->timestamps();
            $table->unique(['test_id', 'student_id']);
            $table->index('student_id');
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('class_no');
            $table->string('section', 8)->default('');
            $table->string('msg', 500);
            $table->timestamps();
            $table->index(['class_no', 'section']);
        });

        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('app_id', 30)->unique();
            $table->string('name', 150);
            $table->string('father_name', 150)->default('');
            $table->string('phone', 40)->default('');
            $table->string('email', 190)->default('');
            $table->string('address', 255)->default('');
            $table->unsignedTinyInteger('class_no');
            $table->string('section', 8)->default('A');
            $table->string('student_id', 20);
            $table->string('password');
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('marks');
        Schema::dropIfExists('tests');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('fees');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('admins');
    }
};
