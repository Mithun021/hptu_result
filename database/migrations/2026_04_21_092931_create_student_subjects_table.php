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
        Schema::create('student_subjects', function (Blueprint $table) {
            $table->id();
            // 🔗 Core Mapping
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');

            // 📚 Course Structure
            $table->enum('course_type', ['Semester Based', 'Annual Based']);
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('branch_id');

            // Optional Scheme
            $table->unsignedBigInteger('scheme_id')->nullable();

            // 🎓 Semester Logic (ONLY ONE WILL BE USED)
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->unsignedBigInteger('annual_semester_id')->nullable();

            $table->timestamps();

            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('cascade');

            $table->foreign('subject_id')
                ->references('id')->on('subjects')
                ->onDelete('cascade');

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->onDelete('cascade');

            $table->foreign('branch_id')
                ->references('id')->on('branches')
                ->onDelete('cascade');

            $table->foreign('scheme_id')
                ->references('id')->on('schemes')
                ->nullOnDelete();

            $table->foreign('semester_id')
                ->references('id')->on('semesters')
                ->nullOnDelete();

            $table->foreign('annual_semester_id')
                ->references('id')->on('annual_semesters')
                ->nullOnDelete();

            $table->unique([
                'student_id',
                'subject_id',
                'course_id',
                'branch_id',
                'semester_id',
                'annual_semester_id'
            ], 'student_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_subjects');
    }
};
