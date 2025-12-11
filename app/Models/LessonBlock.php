<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonBlock extends Model
{
    protected $connection = 'app_mysql';

    protected $fillable = [
        'lesson_id',
        'module_id',
        'topic_id',
        // 'subtopic_id', // ❌ أزلناه لو ما عاد تستخدمه
        'type',
        'body',
        'caption',
        'media_path',
        'media_url',
        'media_mime',
        'media_size',
        'media_duration',
        'position',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function module()
    {
        return $this->belongsTo(LessonModule::class, 'module_id');
    }

    public function topic()
    {
        return $this->belongsTo(LessonTopic::class, 'topic_id');
    }

    // ❌ لا علاقة Subtopic هنا
}
