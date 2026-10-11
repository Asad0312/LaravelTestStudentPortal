<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150)->default('');
            $table->text('body');
            $table->unsignedTinyInteger('class_no')->nullable();  // null = sab classes
            $table->string('section', 8)->default('');             // '' = sab sections
            $table->string('priority', 20)->default('Normal');     // Normal | Important
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();
            $table->index(['class_no', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
