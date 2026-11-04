<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuppleSubject extends Model
{
    protected $table = 'supple_subjects';

    protected $fillable = [
        'supple_student_detail_id',
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
            SuppleStudentDetail::class,
            'supple_student_detail_id'
        );
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
