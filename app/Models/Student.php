<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $table = 'students';

    protected $fillable = [
        'university_roll_no',
        'reg_no',
        'admission_year',
        'completion_year',
        'student_name',
        'father_name',
        'mother_name',
        'abcid',
        'gender',
        'dob',
        'aadhar',
        'current_address',
        'parmanent_address',
        'phone','parent_phone',
        'email',
        'password',
        'nationality',
        'state',
        'city',
        'category',
        'institute_id',
        'profile_image',
        'signature',
        'course_type',
        'course_id',
        'branch_id',
        'semester_id',
        'annual_semester_id',
        'scheme_id',
        'batch_id',
        'pursuing_status',
    ];

    public function getAuthIdentifierName()
    {
        return 'university_roll_no';
    }

    // Hidden attributes when converting to array or JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

     /**
     * Hash password automatically when setting it
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'dob' => 'date',
    ];

    /**
     * Relationships
     */

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function annual_semester()
    {
        return $this->belongsTo(AnnualSemester::class);
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
