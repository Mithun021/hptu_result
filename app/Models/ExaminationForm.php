<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminationForm extends Model
{
    protected $table = 'examination_form';

    protected $fillable = [
        'exam_id',
        'exam_type',
        'student_id',
        'course_id',
        'scheme_id',
        'branch_id',
        'semester_id',
        'batch_id',
        'institute_id',
        'exam_center_id',
        'exam_fee',
        'late_fee',
        'total_exam_fee',
        'last_appearning_session',
        'last_appearing_year',
        'form_id',
        'transaction_id',
        'transaction_date',
        'payment_status',
    ];

    protected $casts = [
        'exam_fee' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'total_exam_fee' => 'decimal:2',
        'transaction_date' => 'datetime',
        'payment_status' => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class, 'institute_id');
    }

    public function examCenter()
    {
        return $this->belongsTo(Institute::class, 'exam_center_id');
    }

    public function choosedSubjects()
    {
        return $this->hasMany(
            ExaminationFormChoosedSubject::class,
            'examination_form_id'
        );
    }
}
