<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';
    
    protected $fillable = [
        'course',
        'name',
        'course_code',
        'course_type',
    ];

    public function semesters()
    {
        return $this->belongsToMany(Semester::class, 'course_semester', 'course_id', 'semester_id');
    }

    public function annual_semesters()
    {
        return $this->belongsToMany(AnnualSemester::class, 'course_annual_semester', 'course_id', 'annual_semester_id');
    }

    public function schemes()
    {
        return $this->belongsToMany(Scheme::class, 'course_scheme', 'course_id', 'scheme_id');
    }

    public function institutes()
    {
        return $this->belongsToMany(Institute::class, 'institute_courses', 'course_id', 'institute_id');
    }

    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'course_batches', 'course_id', 'batch_id');
    }
    
}
