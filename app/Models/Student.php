<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'full_name',
        'academic_id',
        'gender',
        'birthdate',
        'email',
        'status',
        'grade',
        'class_section',
        'address_governorate',
        'address_city',
        'address_street',
        'guardian_name',
        'guardian_relation',
        'guardian_relation_other',
        'guardian_phone',
        'performance_avg',
        'attendance_rate',
        'photo_path',
        'guardian_phones',
        'notes',
    ];

    protected $casts = [
        'birthdate'       => 'date',
        'guardian_phones' => 'array',
        'performance_avg' => 'decimal:2',
        'attendance_rate' => 'decimal:2',
    ];
}
