<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultGpSheet extends Model
{
    protected $table = 'result_gp_sheet';

    protected $fillable = [
        'student_id',
        'subject_id',
        'credits',
        'result',
        'result_gp',
        'grade',
        'gpa',
        'cgpa',
        'exam_id',
        'course_type',
        'course_id',
        'scheme_id',
        'branch_id',
        'semester_id',
        'annual_semester_id',
        'batch_id',
        'institute_id'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function annualSemester()
    {
        return $this->belongsTo(AnnualSemester::class, 'annual_semester_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class, 'institute_id');
    }
}
