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
        Schema::create('exam_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                ->constrained('courses')
                ->restrictOnDelete();

            $table->decimal('regular_exam_fee', 10, 2)->default(0);

            $table->decimal('reappear_theory_exam_fee', 10, 2)->default(0);
            $table->decimal('reappear_theory_max_exam_fee', 10, 2)->default(0);

            $table->decimal('reappear_practical_exam_fee', 10, 2)->default(0);
            $table->decimal('reappear_practical_max_exam_fee', 10, 2)->default(0);

            $table->decimal('special_chance_theory_exam_fee', 10, 2)->default(0);
            $table->decimal('special_chance_theory_max_exam_fee', 10, 2)->default(0);

            $table->decimal('special_chance_practical_exam_fee', 10, 2)->default(0);
            $table->decimal('special_chance_practical_max_exam_fee', 10, 2)->default(0);

            $table->date('exam_start_date')->nullable();
            $table->date('exam_end_date')->nullable();

            $table->date('late_fee_start1')->nullable();
            $table->date('late_fee_end1')->nullable();
            $table->decimal('late_fee_amount1', 10, 2)->default(0)->nullable();

            $table->date('late_fee_start2')->nullable();
            $table->date('late_fee_end2')->nullable();
            $table->decimal('late_fee_amount2', 10, 2)->default(0)->nullable();

            $table->date('late_fee_start3')->nullable();
            $table->date('late_fee_end3')->nullable();
            $table->decimal('late_fee_amount3', 10, 2)->default(0)->nullable();

            $table->timestamps();
        });


        // Exam Fees Mapped Schemes
        Schema::create('exam_fees_mapped_schemes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_fee_id')
                ->constrained('exam_fees')
                ->cascadeOnDelete();

            $table->foreignId('scheme_id')
                ->constrained('schemes')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['exam_fee_id', 'scheme_id'], 'exam_fee_scheme_unique');
        });


        // Exam Fees Mapped Branches
        Schema::create('exam_fees_mapped_branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_fee_id')
                ->constrained('exam_fees')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['exam_fee_id', 'branch_id'], 'exam_fee_branch_unique');
        });


        // Exam Fees Mapped Semesters
        Schema::create('exam_fees_mapped_semester', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_fee_id')
                ->constrained('exam_fees')
                ->cascadeOnDelete();

            $table->foreignId('semester_id')
                ->constrained('semesters')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['exam_fee_id', 'semester_id'], 'exam_fee_semester_unique');
        });


        // Exam Fees Mapped Sessions (Batches)
        Schema::create('exam_fees_mapped_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_fee_id')
                ->constrained('exam_fees')
                ->cascadeOnDelete();

            $table->foreignId('batch_id')
                ->constrained('batches')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['exam_fee_id', 'batch_id'], 'exam_fee_batch_unique');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_fees_mapped_sessions');
        Schema::dropIfExists('exam_fees_mapped_semester');
        Schema::dropIfExists('exam_fees_mapped_branches');
        Schema::dropIfExists('exam_fees_mapped_schemes');
        Schema::dropIfExists('exam_fees');
    }
};
