<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    // 👈 مهم: ربط الموديل بقواعد بيانات التطبيق
    protected $connection = 'app_mysql';

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'body',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function senderStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'sender_id')
            ->where('sender_type', 'student');
    }

    public function senderTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'sender_id')
            ->where('sender_type', 'teacher');
    }
}
