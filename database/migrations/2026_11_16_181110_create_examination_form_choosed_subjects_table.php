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
        Schema::create('examination_form_choosed_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_form_id')
                ->constrained('examination_form')
                ->cascadeOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->restrictOnDelete();
            $table->timestamps();
            $table->unique(
                ['examination_form_id', 'subject_id'],
                'exam_form_subject_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examination_form_choosed_subjects');
    }
};
