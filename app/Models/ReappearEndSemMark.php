<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReappearEndSemMark extends Model
{
    protected $table = 'reappear_end_sem_marks';

    protected $fillable = [

        'exam_id',

        'student_id',

        'subject_id',

        'institute_id',

        'course_type',

        'course_id',

        'branch_id',

        'batch_id',

        'semester_id',

        'annual_semester_id',

        'scheme_id',

        'marks_type',

        'max_marks',

        'obtained_marks',

        'grace_marks',

        'exam_status',

        'retotalling_obtained_marks',
        'retotalling_upload_by',
        'retotalling_marks_locked',

        'is_locked',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function retotalling_upload_by()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
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
        return $this->belongsTo(AnnualSemester::class, 'annual_semester_id');
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }
}
