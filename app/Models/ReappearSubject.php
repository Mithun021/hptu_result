<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReappearSubject extends Model
{
    protected $table = 'reappear_subjects';

    protected $fillable = [
        'reappear_student_detail_id',
        'subject_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function suppleStudentDetail()
    {
        return $this->belongsTo(
            ReappearStudentDetail::class,
            'reappear_student_detail_id'
        );
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
