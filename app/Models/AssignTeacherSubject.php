<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignTeacherSubject extends Model
{
    use HasFactory;

    protected $table = 'assign_teacher_subjects';

    protected $fillable = [
        'teacher_id',
        'institute_id',
        'course_id',
        'scheme_id',
        'branch_id',
        'semester_id',
        'annual_semester_id',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function teacher()
    {
        return $this->belongsTo(Institute::class, 'teacher_id');
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class, 'institute_id');
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

    /* ================= BATCH RELATIONSHIPS ================= */

    public function batchList()
    {
        return $this->hasMany(
            AssignTeacherSubjectBatch::class,
            'assign_teacher_subject_id'
        );
    }

    public function batches()
    {
        return $this->belongsToMany(
            Batch::class,
            'assign_teacher_subject_batches',
            'assign_teacher_subject_id',
            'batch_id'
        )->withTimestamps();
    }

    

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function annualSemester()
    {
        return $this->belongsTo(AnnualSemester::class, 'annual_semester_id');
    }

    public function subjectList()
    {
        return $this->hasMany(
            AssignTeachSubjectList::class,
            'assign_teacher_subject_id'
        );
    }

    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'assign_teach_subject_list',
            'assign_teacher_subject_id',
            'subject_id'
        )->withTimestamps();
    }
}
