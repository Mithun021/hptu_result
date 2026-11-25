<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReappearStudentDetail extends Model
{
    protected $table = 'reappear_student_details';

    protected $fillable = [
        'student_id',
        'exam_id',
        'course_type',
        'course_id',
        'scheme_id',
        'branch_id',
        'semester_id',
        'annual_semester_id',
        'batch_id',
        'institute_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function annualSemester()
    {
        return $this->belongsTo(AnnualSemester::class, 'annual_semester_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function reappearSubjects()
    {
        return $this->hasMany(ReappearSubject::class, 'reappear_student_detail_id');
    }
}
