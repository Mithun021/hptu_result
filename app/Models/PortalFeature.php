<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalFeature extends Model
{
    protected $table = 'portal_features';

    protected $fillable = [
        'cutlist',
        'attendance_sheet',
        'profile',
    ];

    protected $casts = [
        'cutlist' => 'boolean',
        'attendance_sheet' => 'boolean',
        'profile' => 'boolean',
    ];
}
