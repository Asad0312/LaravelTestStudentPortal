<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id', 20)->unique();
            $table->string('name', 150);
            $table->string('father_name', 150)->default('');
            $table->enum('gender', ['Male', 'Female', 'Other'])->default('Male');
            $table->string('phone', 40)->default('');
            $table->string('whatsapp', 40)->default('');
            $table->string('email', 190)->default('');
            $table->string('address', 255)->default('');
            $table->string('designation', 120)->default('Teacher');
            $table->string('subject', 120)->default('');
            $table->string('qualification', 190)->default('');
            $table->string('cnic', 30)->default('');
            $table->date('joining_date')->nullable();
            $table->unsignedInteger('monthly_salary')->default(0);
            $table->string('status', 20)->default('Active');
            $table->text('notes')->nullable();
            $table->string('avatar_seed', 150)->default('');
            $table->timestamps();
        });

        Schema::create('teacher_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->char('month', 7);
            $table->unsignedInteger('monthly_salary')->default(0);
            $table->unsignedInteger('paid_amount')->default(0);
            $table->timestamps();
            $table->unique(['teacher_id', 'month']);
            $table->index('month');
        });

        Schema::create('teacher_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->char('month', 7);
            $table->unsignedInteger('amount');
            $table->date('paid_on');
            $table->string('method', 40)->default('Cash');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_payments');
        Schema::dropIfExists('teacher_salaries');
        Schema::dropIfExists('teachers');
    }
};