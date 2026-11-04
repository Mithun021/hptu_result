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
        Schema::create('student_result_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            $table->decimal('gpa', 5, 3)->nullable();
            $table->decimal('cgpa', 5, 3)->nullable();

            $table->string('result_status')->nullable();
            $table->string('grade_status')->nullable();
            $table->string('remarks')->nullable();

            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('course_type', 50);

            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('scheme_id')->nullable()->constrained('schemes')->nullOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->foreignId('annual_semester_id')->nullable()->constrained('annual_semesters')->nullOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->tinyInteger('result_declare')->default(0)->nullable();

            $table->timestamps();
            $table->unique([
                'student_id',
                'exam_id',
                'course_id',
                'branch_id',
                'batch_id',
                'semester_id',
                'annual_semester_id'
            ], 'unique_student_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_result_summary');
    }
};
