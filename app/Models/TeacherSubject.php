<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    protected $table = 'teacher_subjects';
    protected $fillable = [
        'institute_id',
        'teacher_id',
        'subject_id',
    ];

    public function institute()
    {   
        return $this->belongsTo(Institute::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Institute::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

}
