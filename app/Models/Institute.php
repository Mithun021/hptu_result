<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Institute extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'institutes';

    protected $fillable = [
        'name',
        'sort_name',
        'email',
        'phone',
        'city',
        'password',
        'account_type',
        'teacher_name',
        'institute_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_institute');
    }

    public function parentInstitute()
    {
        return $this->belongsTo(Institute::class, 'institute_id', 'id');
    }

    public function assignedSubjects()
    {
        return $this->hasMany(TeacherSubject::class, 'teacher_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_subjects',
            'teacher_id',
            'subject_id'
        )->withPivot('institute_id')
        ->withTimestamps();
    }
    
}
