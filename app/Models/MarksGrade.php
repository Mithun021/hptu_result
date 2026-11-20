<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarksGrade extends Model
{
    use HasFactory;

    protected $table = 'marks_grades';

    protected $fillable = [
        'course_id',
        'scheme_id',
        'grade_name',
        'percent_from',
        'percent_upto',
        'grade_point',
        'grade_description',
    ];

    protected $casts = [
        'percent_from' => 'float',
        'percent_upto' => 'float',
        'grade_point'  => 'float',
    ];

    /**
     * Relation: MarksGrade belongs to Course
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Relation: MarksGrade belongs to Scheme
     */
    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
}
