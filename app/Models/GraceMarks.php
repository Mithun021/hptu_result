<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GraceMarks extends Model
{
    protected $table = 'grace_marks';

    protected $fillable = [
        'course_type',
        'addon_marks_type',
        'addon_marks_on',
        'max_allowed',
        'max_marks',
    ];
}
