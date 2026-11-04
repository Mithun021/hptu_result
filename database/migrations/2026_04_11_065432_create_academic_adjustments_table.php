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
        Schema::create('academic_adjustments', function (Blueprint $table) {
            $table->id();
            /*
            =====================================
            CORE RELATION
            =====================================
            */
            $table->enum('adjustment_scope', [
                'internal',
                'external'
            ])->default('internal');

            $table->string('marks_type')->nullable();
            
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            /*
            =====================================
            FULL ACADEMIC CONTEXT
            =====================================
            */
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('course_type', 50)->nullable();

            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('annual_semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('scheme_id')->nullable()->constrained()->nullOnDelete();

            /*
            =====================================
            ADJUSTMENT DETAILS
            =====================================
            */
            $table->unsignedInteger('adjustment_marks')->default(0);

            $table->enum('adjustment_type', [
                'grace',
                'moderation',
                'sports',
                'ncc',
                'cultural',
                'medical',
                'revaluation',
                'other'
            ]);
            /*
            =====================================
            CONTROL
            =====================================
            */
            $table->boolean('is_approved')->default(false);

            $table->foreignId('approved_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

            $table->timestamps();

             $table->unique([
                'exam_id',
                'student_id',
                'subject_id',
                'institute_id',
                'course_id',
                'branch_id',
                'batch_id',
                'semester_id',
                'annual_semester_id',
                'scheme_id'
            ], 'unique_academic_adjustment_context');

            /*
            =====================================
            INDEX (PERFORMANCE)
            =====================================
            */
            $table->index(['exam_id','student_id','subject_id'], 'acad_adj_core_idx');

            $table->index(['course_id','branch_id','batch_id'], 'acad_adj_course_idx');

            $table->index(['semester_id','annual_semester_id'], 'acad_adj_sem_idx');

            $table->index(['institute_id','exam_id'], 'acad_adj_inst_idx');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_adjustments');
    }
};
