<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExamCenter extends Model
{
    protected $table = 'exam_center';

    protected $fillable = [
        'student_type',
        'institute_id',
        'course_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Main Relationships
    |--------------------------------------------------------------------------
    */

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Mapping Relationships
    |--------------------------------------------------------------------------
    */

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(
            Branch::class,
            'exam_center_branches',
            'exam_center_id',
            'branch_id'
        );
    }

    public function semesters(): BelongsToMany
    {
        return $this->belongsToMany(
            Semester::class,
            'exam_center_semesters',
            'exam_center_id',
            'semester_id'
        );
    }

    public function batches(): BelongsToMany
    {
        return $this->belongsToMany(
            Batch::class,
            'exam_center_session',
            'exam_center_id',
            'batch_id'
        );
    }

    public function schemes(): BelongsToMany
    {
        return $this->belongsToMany(
            Scheme::class,
            'exam_center_scheme',
            'exam_center_id',
            'scheme_id'
        );
    }

    public function colleges(): BelongsToMany
    {
        return $this->belongsToMany(
            Institute::class,
            'exam_center_colleges',
            'exam_center_id',
            'institute_id'
        );
    }
}
