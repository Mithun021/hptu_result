<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MidSemExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'mid_sem_exam_name',
        'course_type',
        'status'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
