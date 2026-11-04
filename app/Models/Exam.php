<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $table = 'exams';

    protected $fillable = [
        'name',
        'sort_name',
        'admit_card_name',
        'exam_resultsheet_name',
        'dmc_name',
        'supple_result_sheet',
        'reconsile_result_sheet',
        'exam_month',
        'exam_year',
        'is_active',
        'show_admitcard',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_admitcard' => 'boolean',
    ];
}
