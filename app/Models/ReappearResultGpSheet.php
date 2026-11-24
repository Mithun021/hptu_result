<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReappearResultGpSheet extends Model
{
    use HasFactory;

    protected $table = 'reappear_result_gp_sheet';

    protected $fillable = [

        'student_id',
        'subject_id',
        'credits',

        'result',
        'is_reappear',
        'earn_credit_point',
        'grade',
        'gain_percentage',
        'credit_score',
        'retotalling_credits',
        'retotalling_result',
        'retotalling_is_reappear',
        'retotalling_earn_credit_point',
        'retotalling_grade',
        'retotalling_gain_percentage',
        'retotalling_credit_score',
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

    ];

    protected $casts = [
        'is_supple' => 'boolean',
        'result_gp' => 'decimal:3',
    ];

    public function retotalling_upload_by()
    {
        return $this->belongsTo(User::class);
    }

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
