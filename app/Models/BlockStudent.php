<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockStudent extends Model
{
    protected $table = 'block_students';

    protected $fillable = [
        'exam_id',
        'hold_type',
        'student_id',
        'semester_id',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
