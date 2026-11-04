<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubject extends Model
{
    use HasFactory;

    protected $table = 'student_subjects';

    /*
    |--------------------------------------------------------------------------
    | 🛡️ Mass Assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'student_id',
        'subject_id',
        'course_type',
        'course_id',
        'branch_id',
        'scheme_id',
        'semester_id',
        'annual_semester_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔄 Type Casting
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'student_id'         => 'integer',
        'subject_id'         => 'integer',
        'course_id'          => 'integer',
        'branch_id'          => 'integer',
        'scheme_id'          => 'integer',
        'semester_id'        => 'integer',
        'annual_semester_id' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 Relationships
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function annualSemester()
    {
        return $this->belongsTo(AnnualSemester::class);
    }
}
