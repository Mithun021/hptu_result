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
        Schema::create('reappear_subjects', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('reappear_student_detail_id');
            $table->unsignedBigInteger('subject_id');

            $table->timestamps();

            $table->foreign('reappear_student_detail_id')
                ->references('id')
                ->on('reappear_student_details')
                ->cascadeOnDelete();

            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects');

            // Prevent duplicate subject for same student detail
            $table->unique(
                ['reappear_student_detail_id', 'subject_id'],
                'unique_supple_subject'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reappear_subjects');
    }
};
