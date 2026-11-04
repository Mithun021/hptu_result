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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('course_type', 50)->nullable();
            // Foreign keys
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('annual_semester_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('scheme_id')->nullable()->constrained()->cascadeOnDelete();

            // Subject details
            $table->string('name');
            $table->enum('type', ['Theory', 'Practical'])->default('Theory');
            $table->string('subject_code');
            $table->unsignedTinyInteger('credits')->default(0);
            $table->unsignedSmallInteger('mid_sem_marks1')->default(0);
            $table->unsignedSmallInteger('mid_sem_marks2')->default(0)->nullable();
            $table->unsignedSmallInteger('mid_sem_marks3')->default(0)->nullable();
            // Annual Internal
            $table->unsignedSmallInteger('internal1')->default(0)->nullable();
            $table->unsignedSmallInteger('internal2')->default(0)->nullable();
            $table->unsignedSmallInteger('internal3')->default(0)->nullable();
            // Annual External
            $table->unsignedSmallInteger('paper1')->default(0)->nullable();
            $table->unsignedSmallInteger('paper2')->default(0)->nullable();

            // Annual Practical
            $table->unsignedSmallInteger('practical_paper1')->default(0)->nullable();
            $table->unsignedSmallInteger('practical_paper2')->default(0)->nullable();

            $table->unsignedSmallInteger('internal_marks')->default(0);
            $table->unsignedSmallInteger('external_marks')->default(0);
            $table->unsignedSmallInteger('total')->default(0);

            $table->boolean('is_nc')->nullable()->default(null);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
