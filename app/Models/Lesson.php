<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    // 🔹 قاعدة بيانات التطبيق (app_mysql)
    protected $connection = 'app_mysql';

    protected $fillable = [
        'teacher_id',
        'assignment_id',
        'class_module_id', // 👈 جديد
        'class_section_id',
        'subject_id',
        'title',
        'status',
        'published_at',
        'meta',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meta'         => 'array',
    ];

    public function modules()
    {
        return $this->hasMany(LessonModule::class)->orderBy('position');
    }

    
    public function classModule()
{
    return $this->belongsTo(ClassModule::class, 'class_module_id');
}


    public function topics()
    {
        return $this->hasMany(LessonTopic::class)->orderBy('position');
    }

    // ❌ لا يوجد Subtopics هنا

    public function blocks()
    {
        return $this->hasMany(LessonBlock::class)->orderBy('position');
    }

    // 🔗 مرجع للأستاذ من قاعدة edulearn_db (اختياري للاستخدام الداخلي)
    public function teacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id');
    }

    public function classSection()
    {
        return $this->belongsTo(\App\Models\ClassSection::class, 'class_section_id');
    }

    public function subject()
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }
}
