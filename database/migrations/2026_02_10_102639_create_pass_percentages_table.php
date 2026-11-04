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
        Schema::create('pass_percentages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->decimal('overall_pass_percentage', 5, 2);
            $table->decimal('theory_min_percentage', 5, 2)->default(20);
            $table->timestamps();
            $table->foreign('course_id')
              ->references('id')
              ->on('courses')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pass_percentages');
    }
};
