<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicAdjustment extends Model
{
    use HasFactory;

    protected $table = "academic_adjustments";

    protected $fillable = [

        /*
        ==========================
        CORE
        ==========================
        */
        'exam_id',
        'marks_type',
        'student_id',
        'subject_id',

        /*
        ==========================
        ACADEMIC CONTEXT
        ==========================
        */
        'institute_id',
        'course_type',

        'course_id',
        'branch_id',
        'batch_id',

        'semester_id',
        'annual_semester_id',
        'scheme_id',

        /*
        ==========================
        ADJUSTMENT
        ==========================
        */
        'adjustment_marks',
        'adjustment_type',
        'adjustment_scope',

        /*
        ==========================
        CONTROL
        ==========================
        */
        'is_approved',
        'approved_by',
        'remarks',
    ];

    /*
    =====================================
    RELATIONS
    =====================================
    */

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function annualSemester()
    {
        return $this->belongsTo(
            AnnualSemester::class,
            'annual_semester_id'
        );
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
