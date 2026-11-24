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
        'scheme_id',
        'overall_pass_percentage',
        'percentage_type',
    ];

    /**
     * Relation: PassPercentage belongs to Course
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Relation: PassPercentage belongs to Scheme
     */
    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
}
