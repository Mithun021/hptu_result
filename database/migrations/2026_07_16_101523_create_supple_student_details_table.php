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
        Schema::create('supple_student_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('exam_id');

            $table->string('course_type');

            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('scheme_id')->nullable();
            $table->unsignedBigInteger('branch_id');

            $table->unsignedBigInteger('semester_id')->nullable();
            $table->unsignedBigInteger('annual_semester_id')->nullable();

            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('institute_id');

            $table->timestamps();

            // Foreign Keys
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();

            $table->foreign('course_id')->references('id')->on('courses');
            $table->foreign('scheme_id')->references('id')->on('schemes');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->foreign('annual_semester_id')->references('id')->on('annual_semesters');
            $table->foreign('batch_id')->references('id')->on('batches');
            $table->foreign('institute_id')->references('id')->on('institutes');

            // Prevent duplicate mapping for same student in same exam
            $table->unique(['student_id', 'exam_id'], 'unique_supple_student_exam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supple_student_details');
    }
};
