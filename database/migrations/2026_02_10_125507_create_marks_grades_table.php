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
        Schema::create('marks_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');         
            $table->string('grade_name', 20);                  
            $table->decimal('percent_from', 5, 2);                
            $table->decimal('percent_upto', 5, 2);                
            $table->decimal('grade_point', 4, 2)->nullable();                  
            $table->string('grade_description', 20)->nullable();
            $table->timestamps();
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');

            // Optional: ek course me same range duplicate na ho
            $table->unique(['course_id', 'percent_from', 'percent_upto']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marks_grades');
    }
};
