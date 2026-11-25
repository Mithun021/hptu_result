<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentResultSummary extends Model
{
    protected $table = 'student_result_summary';

    protected $fillable = [
        'student_id',
        'sgpa',
        'cgpa',
        'result_status',
        'grade_status',
        'remarks',

        'retotalling_sgpa',
        'retotalling_cgpa',
        'retotalling_result_status',
        'retotalling_grade_status',
        'retotalling_remarks',
        'retotalling_upload_by ',

        'exam_id',
        'course_type',
        'course_id',
        'scheme_id',
        'branch_id',
        'semester_id',
        'annual_semester_id',
        'batch_id',
        'institute_id',
        'result_declare'
    ];

    /*
    =============================
    RELATIONS
    =============================
    */

    public function retotalling_upload_by()
    {
        return $this->belongsTo(User::class);
    }

    // Student Relation
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Course Relation
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Branch Relation
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    // Exam Relation
    public function exam()
    {
        return $this->belongsTo(Exam::class);
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

    /*
    =============================
    SCOPES (Optional but useful)
    =============================
    */

    public function scopeFilter($query, $request)
    {
        return $query
            ->when($request->exam_id, fn($q) => $q->where('exam_id', $request->exam_id))
            ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->batch_id, fn($q) => $q->where('batch_id', $request->batch_id))
            ->when($request->semester_id, fn($q) => $q->where('semester_id', $request->semester_id))
            ->when($request->annual_semester_id, fn($q) => $q->where('annual_semester_id', $request->annual_semester_id));
    }
}
