<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExamFee extends Model
{
    protected $table = 'exam_fees';

    protected $fillable = [
        'course_id',

        'regular_exam_fee',

        'reappear_theory_exam_fee',
        'reappear_theory_max_exam_fee',

        'reappear_practical_exam_fee',
        'reappear_practical_max_exam_fee',

        'special_chance_theory_exam_fee',
        'special_chance_theory_max_exam_fee',

        'special_chance_practical_exam_fee',
        'special_chance_practical_max_exam_fee',

        'exam_start_date',
        'exam_end_date',

        'late_fee_start1',
        'late_fee_end1',

        'late_fee_start2',
        'late_fee_end2',

        'late_fee_start3',
        'late_fee_end3',

        'late_fee_amount1',
        'late_fee_amount2',
        'late_fee_amount3',
    ];

    protected function casts(): array
    {
        return [
            'regular_exam_fee' => 'decimal:2',

            'reappear_theory_exam_fee' => 'decimal:2',
            'reappear_theory_max_exam_fee' => 'decimal:2',

            'reappear_practical_exam_fee' => 'decimal:2',
            'reappear_practical_max_exam_fee' => 'decimal:2',

            'special_chance_theory_exam_fee' => 'decimal:2',
            'special_chance_theory_max_exam_fee' => 'decimal:2',

            'special_chance_practical_exam_fee' => 'decimal:2',
            'special_chance_practical_max_exam_fee' => 'decimal:2',

            'exam_start_date' => 'date',
            'exam_end_date' => 'date',

            'late_fee_start1' => 'date',
            'late_fee_end1' => 'date',

            'late_fee_start2' => 'date',
            'late_fee_end2' => 'date',

            'late_fee_start3' => 'date',
            'late_fee_end3' => 'date',


            'late_fee_amount1' => 'decimal:2',
            'late_fee_amount2' => 'decimal:2',
            'late_fee_amount3' => 'decimal:2',

        ];
    }

    // Course Relationship
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // Scheme Relationship
    public function schemes(): BelongsToMany
    {
        return $this->belongsToMany(
            Scheme::class,
            'exam_fees_mapped_schemes',
            'exam_fee_id',
            'scheme_id'
        )->withTimestamps();
    }

    // Branch Relationship
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(
            Branch::class,
            'exam_fees_mapped_branches',
            'exam_fee_id',
            'branch_id'
        )->withTimestamps();
    }

    // Semester Relationship
    public function semesters(): BelongsToMany
    {
        return $this->belongsToMany(
            Semester::class,
            'exam_fees_mapped_semester',
            'exam_fee_id',
            'semester_id'
        )->withTimestamps();
    }

    // Batch / Session Relationship
    public function batches(): BelongsToMany
    {
        return $this->belongsToMany(
            Batch::class,
            'exam_fees_mapped_sessions',
            'exam_fee_id',
            'batch_id'
        )->withTimestamps();
    }
}
