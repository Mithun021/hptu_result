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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Full Exam Name
            $table->string('sort_name', 50)->nullable(); // Short Name (e.g. B.Tech, JEE)
            $table->string('admit_card_name')->nullable(); // Admit Card Display Name
            $table->string('exam_resultsheet_name')->nullable(); // Result Sheet Name
            $table->string('dmc_name')->nullable(); // DMC Name
            $table->string('exam_month')->nullable();
            $table->boolean('is_active')->default(true); // yes/no
            $table->boolean('show_admitcard')->default(false); // yes/no

            $table->string('description', 255)->nullable(); // max 255 characters
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
