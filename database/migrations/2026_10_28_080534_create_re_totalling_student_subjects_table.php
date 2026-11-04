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
        Schema::create('re_totalling_student_subjects', function (Blueprint $table) {
            $table->id();
             $table->foreignId('re_totalling_student_id')
                ->constrained('re_totalling_student')
                ->cascadeOnDelete();

            $table->foreignId('subjects_id')
                ->constrained('subjects')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('re_totalling_student_subjects');
    }
};
