<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('period_type')->default('Quarterly'); // Quarterly, Semester, Annual, Monthly
            $table->string('period_label'); // e.g. Q3 2026, Semester 2 2026
            $table->date('start_date');
            $table->date('end_date');
            $table->string('rating'); // Sangat Baik (A), Baik (B), Cukup (C), Perlu Peningkatan (D), Kurang (E)
            $table->text('summary');
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->string('status')->default('Final'); // Draft, Final
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
