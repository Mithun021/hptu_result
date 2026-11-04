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
        Schema::create('end_sem_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            // Academic Context
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('course_type',50)->nullable();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('annual_semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('scheme_id')->nullable()->constrained()->nullOnDelete();

            // Marks
            $table->string('marks_type', 60)->nullable();
            $table->unsignedInteger('max_marks');
            $table->unsignedInteger('obtained_marks')->nullable();

            $table->enum('exam_status',['present','absent'])->default('present');

            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('end_sem_marks');
    }
};
