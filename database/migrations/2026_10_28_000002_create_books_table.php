<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->enum('stream', ['school', 'madarsa']);
            $table->unsignedTinyInteger('class_level')->nullable();
            $table->char('madarsa_section', 1)->nullable();
            $table->string('title');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};