<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminationFormChoosedSubject extends Model
{
    protected $table = 'examination_form_choosed_subjects';

    protected $fillable = [
        'examination_form_id',
        'subject_id',
    ];

    public function examinationForm()
    {
        return $this->belongsTo(
            ExaminationForm::class,
            'examination_form_id'
        );
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
