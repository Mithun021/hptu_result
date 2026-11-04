<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReTotallingStudentSubject extends Model
{
    protected $table = 're_totalling_student_subjects';

    protected $fillable = [
        're_totalling_student_id',
        'subjects_id',
    ];

    public function reTotallingStudent(): BelongsTo
    {
        return $this->belongsTo(
            ReTotallingStudent::class,
            're_totalling_student_id'
        );
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subjects_id');
    }
}
