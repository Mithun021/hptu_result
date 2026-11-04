<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuppleResultSummary extends Model
{
    use HasFactory;

    protected $table = 'supple_student_result_summary';

    protected $fillable = [

        'student_id',

        'gpa',
        'cgpa',
        'is_supple',
        'result_status',
        'grade_status',
        'remarks',

        'exam_id',
        'course_type',
        'course_id',
        'scheme_id',
        'branch_id',
        'semester_id',
        'annual_semester_id',
        'batch_id',
        'institute_id',

        'result_declare',

    ];

    protected $casts = [

        'gpa' => 'decimal:3',
        'cgpa' => 'decimal:3',
        'result_declare' => 'boolean',

    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class,'exam_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class,'course_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class,'batch_id');
    }
    
    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class, 'institute_id');
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
}
