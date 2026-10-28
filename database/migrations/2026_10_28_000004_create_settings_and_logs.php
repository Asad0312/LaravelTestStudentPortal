<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('to_phone')->nullable();
            $table->enum('channel', ['whatsapp', 'sms'])->default('whatsapp');
            $table->string('type')->nullable();
            $table->text('body')->nullable();
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->decimal('cost', 8, 4)->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('portal_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('stream', ['school', 'madarsa', 'both'])->nullable();
            $table->unsignedTinyInteger('class_level')->nullable();
            $table->char('madarsa_section', 1)->nullable();
            $table->text('text');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_alerts');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('settings');
    }
};