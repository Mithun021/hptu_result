<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';
    protected $fillable = [
        'course_id',
        'name',
        'branch_code'
    ];
    
    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function institutes()
    {
        return $this->belongsToMany(Institute::class, 'branch_institute');
    }
}
