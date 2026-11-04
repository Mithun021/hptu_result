<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MidSemMark extends Model
{
    protected $table = 'mid_sem_marks';
    protected $fillable = [
        'exam_id',
        'mid_sem_exam_id',
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
        'exam_status',
        'obtained_marks',
        'grace_marks',
        'grade',
        'is_locked',
        'university_forword',
        'upload_by'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    
    public function upload_by()
    {
        return $this->belongsTo(Institute::class);
    }
}
