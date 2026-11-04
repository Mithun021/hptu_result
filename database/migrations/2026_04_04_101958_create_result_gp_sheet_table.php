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
        Schema::create('result_gp_sheet', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->tinyInteger('credits')->nullable();
            $table->string('result', 20)->nullable();
            $table->decimal('result_gp', 5, 2)->nullable();

            $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->string('course_type', 50)->nullable();

            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('scheme_id')->nullable()->constrained('schemes')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->foreignId('annual_semester_id')->nullable()->constrained('annual_semesters')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('institute_id')->nullable()->constrained('institutes')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_gp_sheet');
    }
};
