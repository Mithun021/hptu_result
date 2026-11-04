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
        Schema::create('re_totalling_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnDelete();

            $table->enum('result_type', ['regular', 'supplementary'])
                ->default('regular');

            $table->foreignId('batch_id')
                ->constrained('batches')
                ->cascadeOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('scheme_id')
                ->nullable()
                ->constrained('schemes')
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->nullOnDelete();

            $table->foreignId('annual_semester_id')
                ->nullable()
                ->constrained('annual_semesters')
                ->nullOnDelete();

            $table->foreignId('institute_id')
                ->constrained('institutes')
                ->cascadeOnDelete();

            $table->foreignId('upload_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('re_totalling_student');
    }
};
