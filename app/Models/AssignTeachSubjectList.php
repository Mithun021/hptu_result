<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignTeachSubjectList extends Model
{
    use HasFactory;

    protected $table = 'assign_teach_subject_list';

    protected $fillable = [
        'assign_teacher_subject_id',
        'subject_id',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function assignTeacherSubject()
    {
        return $this->belongsTo(
            AssignTeacherSubject::class,
            'assign_teacher_subject_id'
        );
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
