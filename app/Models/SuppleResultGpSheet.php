<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuppleResultGpSheet extends Model
{
    use HasFactory;

    protected $table = 'supple_result_gp_sheet';

    protected $fillable = [

        'student_id',
        'subject_id',
        'credits',

        'result',
        'is_supple',
        'result_gp',
        'grade',

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

    protected $casts = [
        'is_supple' => 'boolean',
        'result_gp' => 'decimal:3',
    ];
    
    public function institute()
    {
        return $this->belongsTo(Institute::class);
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
    
    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class,'batch_id');
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
