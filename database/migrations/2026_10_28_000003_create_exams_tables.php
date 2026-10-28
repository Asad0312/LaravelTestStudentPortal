<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('exam_month');
            $table->enum('stream', ['school', 'madarsa', 'both'])->default('school');
            $table->unsignedTinyInteger('class_level')->nullable();
            $table->char('madarsa_section', 1)->nullable();
            $table->decimal('exam_fee', 10, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_event_id')->constrained('exam_events')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->date('paid_on')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamps();
            $table->unique(['exam_event_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_fees');
        Schema::dropIfExists('exam_events');
    }
};