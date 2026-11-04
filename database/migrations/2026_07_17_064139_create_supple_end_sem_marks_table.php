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
        Schema::create('supple_end_sem_marks', function (Blueprint $table) {
            $table->id();
             // Basic Details
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            // Academic Context
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();

            $table->string('course_type', 50);

            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('semester_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('annual_semester_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('scheme_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Marks
            $table->string('marks_type', 60)->nullable();
            $table->unsignedInteger('max_marks');

           $table->decimal('obtained_marks', 5, 2)
                ->unsigned()
                ->nullable();

            $table->decimal('grace_marks', 5, 2)
                ->unsigned()
                ->nullable();

            $table->enum('exam_status', [
                'present',
                'absent'
            ])->default('present');

            // Lock
            $table->boolean('is_locked')
                ->default(false);
            $table->timestamps();

            $table->unique([
                'exam_id',
                'student_id',
                'subject_id'
            ], 'supple_end_sem_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supple_end_sem_marks');
    }
};
