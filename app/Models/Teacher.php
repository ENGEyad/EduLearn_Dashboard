<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'teacher_code',
        'email',
        'phone',

        'birth_governorate',
        'birthdate',
        'age',

        'qualification',
        'qualification_date',
        'current_school',
        'join_date',
        'current_role',
        'weekly_load',
        'salary',

        'shift',
        'national_id',

        'marital_status',
        'children',
        'district',
        'neighborhood',
        'street',

        'stage',
        'subjects',
        'grades',

        'experience_years',
        'experience_place',

        'status',
        'students_count',
        'avg_student_score',
        'attendance_rate',
    ];

    protected $casts = [
        'subjects' => 'array',
        'grades' => 'array',
        'birthdate' => 'date',
        'qualification_date' => 'date',
        'join_date' => 'date',
    ];
}
