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
        Schema::create('examination_form', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')
                ->constrained('exams')
                ->restrictOnDelete();

            $table->string('exam_type');

            $table->foreignId('student_id')
                ->constrained('students')
                ->restrictOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->restrictOnDelete();

            $table->foreignId('scheme_id')
                ->constrained('schemes')
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('semester_id')
                ->constrained('semesters')
                ->restrictOnDelete();

            $table->foreignId('batch_id')
                ->constrained('batches')
                ->restrictOnDelete();

            $table->foreignId('institute_id')
                ->constrained('institutes')
                ->restrictOnDelete();

            $table->foreignId('exam_center_id')
                ->constrained('institutes')
                ->restrictOnDelete();

            $table->decimal('exam_fee', 10, 2)->default(0);
            $table->decimal('late_fee', 10, 2)->default(0);

            $table->string('last_appearning_session', 50)->nullable();
            $table->string('last_appearing_year', 50)->nullable();

            $table->string('form_id')->nullable()->unique();
            $table->string('transaction_id')->nullable()->unique();

            $table->dateTime('transaction_date')->nullable();

            $table->boolean('payment_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examination_form');
    }
};
