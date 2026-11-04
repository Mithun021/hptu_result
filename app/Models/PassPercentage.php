<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassPercentage extends Model
{
    use HasFactory;

    protected $table = 'pass_percentages';

    protected $fillable = [
        'course_id',
        'overall_pass_percentage',
        'theory_min_percentage',
    ];

    /**
     * Relation: PassPercentage belongs to Course
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
