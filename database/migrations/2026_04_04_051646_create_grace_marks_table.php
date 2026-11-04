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
        Schema::create('grace_marks', function (Blueprint $table) {
            $table->id();
            $table->string('course_type');
            $table->string('addon_marks_type');
            $table->string('addon_marks_on',60);
            $table->integer('max_allowed');
            $table->integer('max_marks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grace_marks');
    }
};
