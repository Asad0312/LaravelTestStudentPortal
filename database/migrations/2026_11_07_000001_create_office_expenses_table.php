<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('category', 60)->default('Misc');
            $table->unsignedInteger('amount')->default(0);
            $table->date('expense_date');
            $table->string('paid_to', 150)->default('');
            $table->string('payment_method', 40)->default('Cash');
            $table->string('reference', 100)->default('');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index('expense_date');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_expenses');
    }
};