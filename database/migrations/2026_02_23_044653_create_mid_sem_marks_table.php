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
        Schema::create('mid_sem_marks', function (Blueprint $table) {

            $table->id();

            // Exam Context
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mid_sem_exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            // Academic Structure
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('course_type', 50)->nullable();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('annual_semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('scheme_id')->nullable()->constrained()->nullOnDelete();

            // Marks Info
            $table->string('marks_type', 40);
            $table->unsignedInteger('max_marks');
            $table->unsignedInteger('obtained_marks')->nullable();
            $table->enum('grade', ['Satisfactory', 'Unsatisfactory'])
                  ->nullable();
            $table->enum('exam_status', ['present', 'absent'])->default('present');

            // Locking
            $table->boolean('is_locked')->default(false);

            $table->timestamps();

            /*
            -----------------------------------------
            FULL CONTEXT UNIQUE CONSTRAINT
            -----------------------------------------
            */

            $table->unique([
                'mid_sem_exam_id',
                'student_id',
                'subject_id',
                'institute_id',
                'course_id',
                'branch_id',
                'batch_id',
                'semester_id',
                'annual_semester_id',
                'scheme_id',
                'marks_type'
            ], 'unique_mid_sem_full_context');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mid_sem_marks');
    }
};
