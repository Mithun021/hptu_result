<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $fillable = [
        'course_type',
        'course_id',
        'branch_id',
        'semester_id',
        'annual_semester_id',
        'scheme_id',
        'name',
        'type',
        'subject_code',
        'theory_credits',
        'practical_credits',
        'credits',
        'mid_sem_marks1',
        'mid_sem_marks2',
        'mid_sem_marks3',

        'internal1',
        'internal2',
        'internal3',
        
        'paper1',
        'paper2',

        'practical_paper1',
        'practical_paper2',

        'internal_marks',
        'external_marks',
        'total',

        'is_nc',
        'orders',
    ];

    /**
     * Relationships
     */

    // Subject belongs to a course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Subject belongs to a branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Subject belongs to a semester
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function annual_semester()
    {
        return $this->belongsTo(AnnualSemester::class);
    }

    // Subject belongs to a scheme
    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }
    
    public function teachers()
    {
        return $this->belongsToMany(
            Institute::class,
            'teacher_subjects',
            'subject_id',
            'teacher_id'
        )->withPivot('institute_id')
        ->withTimestamps();
    }
}
