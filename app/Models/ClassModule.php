<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModule extends Model
{
    protected $connection = 'app_mysql';

    protected $fillable = [
        'teacher_id',
        'assignment_id',
        'class_section_id',
        'subject_id',
        'title',
        'position',
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'class_module_id');
    }

    // اختياري لو تحب ترجع للأستاذ / الإسناد
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function assignment()
    {
        return $this->belongsTo(TeacherClassSubject::class, 'assignment_id');
    }

    
}
