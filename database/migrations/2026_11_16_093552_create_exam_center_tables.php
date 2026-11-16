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
        Schema::create('exam_center', function (Blueprint $table) {
            $table->id();
            $table->string('student_type');

            $table->foreignId('institute_id')
                ->nullable()
                ->constrained('institutes')
                ->restrictOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->restrictOnDelete();

            $table->timestamps();
        });


        // Exam Center Branches
        Schema::create('exam_center_branches', function (Blueprint $table) {
            $table->foreignId('exam_center_id')
                ->constrained('exam_center')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->unique(
                ['exam_center_id', 'branch_id'],
                'exam_center_branch_unique'
            );
        });

        // Exam Center Semesters
        Schema::create('exam_center_semesters', function (Blueprint $table) {
            $table->foreignId('exam_center_id')
                ->constrained('exam_center')
                ->cascadeOnDelete();

            $table->foreignId('semester_id')
                ->constrained('semesters')
                ->restrictOnDelete();

            $table->unique(
                ['exam_center_id', 'semester_id'],
                'exam_center_semester_unique'
            );
        });

        // Exam Center Sessions
        Schema::create('exam_center_session', function (Blueprint $table) {
            $table->foreignId('exam_center_id')
                ->constrained('exam_center')
                ->cascadeOnDelete();

            $table->foreignId('batch_id')
                ->constrained('batches')
                ->restrictOnDelete();

            $table->unique(
                ['exam_center_id', 'batch_id'],
                'exam_center_session_unique'
            );
        });

        // Exam Center Schemes
        Schema::create('exam_center_scheme', function (Blueprint $table) {
            $table->foreignId('exam_center_id')
                ->constrained('exam_center')
                ->cascadeOnDelete();

            $table->foreignId('scheme_id')
                ->constrained('schemes')
                ->restrictOnDelete();

            $table->unique(
                ['exam_center_id', 'scheme_id'],
                'exam_center_scheme_unique'
            );
        });

        // Exam Center Colleges
        Schema::create('exam_center_colleges', function (Blueprint $table) {
            $table->foreignId('exam_center_id')
                ->constrained('exam_center')
                ->cascadeOnDelete();

            $table->foreignId('institute_id')
                ->constrained('institutes')
                ->restrictOnDelete();

            $table->unique(
                ['exam_center_id', 'institute_id'],
                'exam_center_college_unique'
            );
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_center_colleges');
        Schema::dropIfExists('exam_center_scheme');
        Schema::dropIfExists('exam_center_session');
        Schema::dropIfExists('exam_center_semesters');
        Schema::dropIfExists('exam_center_branches');
        Schema::dropIfExists('exam_center');
    }
};
