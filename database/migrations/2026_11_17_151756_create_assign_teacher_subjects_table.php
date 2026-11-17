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
        Schema::create('assign_teacher_subjects', function (Blueprint $table) {
            $table->id();
            // Institute and Teacher
            $table->foreignId('teacher_id')
                ->constrained('institutes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('institute_id')
                ->constrained('institutes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Academic Details
            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('scheme_id')
                ->nullable()
                ->constrained('schemes')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('batch_id')
                ->constrained('batches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Semester Based / Annual Based
            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('annual_semester_id')
                ->nullable()
                ->constrained('annual_semesters')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_teacher_subjects');
    }
};
