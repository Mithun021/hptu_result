<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReTotallingStudent extends Model
{
    protected $table = 're_totalling_student';

    protected $fillable = [
        'student_id',
        'exam_id',
        'result_type',
        'batch_id',
        'course_id',
        'scheme_id',
        'branch_id',
        'semester_id',
        'annual_semester_id',
        'institute_id',
        'upload_by',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function annualSemester(): BelongsTo
    {
        return $this->belongsTo(AnnualSemester::class, 'annual_semester_id');
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class, 'institute_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upload_by');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(
            ReTotallingStudentSubject::class,
            're_totalling_student_id'
        );
    }
}
