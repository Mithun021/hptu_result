<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnualSemester extends Model
{
    protected $table = 'annual_semesters';
    protected $fillable = [
        'name',
    ];
}
