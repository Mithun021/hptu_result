<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignTeacherSubjectBatch extends Model
{
    use HasFactory;

    protected $table = 'assign_teacher_subject_batches';

    protected $fillable = [
        'assign_teacher_subject_id',
        'batch_id',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function assignTeacherSubject()
    {
        return $this->belongsTo(
            AssignTeacherSubject::class,
            'assign_teacher_subject_id'
        );
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }
}
