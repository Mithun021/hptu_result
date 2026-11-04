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
        Schema::create('course_annual_semester', function (Blueprint $table) {
            $table->unsignedBigInteger('annual_semester_id');
            $table->unsignedBigInteger('course_id');
            // foreign keys
            $table->foreign('annual_semester_id')->references('id')->on('annual_semesters')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');

            // composite primary key
            $table->primary(['annual_semester_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_annual_semester');
    }
};
