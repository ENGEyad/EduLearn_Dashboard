<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public Conversation $conversation;

    public function __construct(Message $message, Conversation $conversation)
    {
        $this->message      = $message;
        $this->conversation = $conversation;
    }

    /**
     * Flutter يشترك على:
     * conversation.{id}
     */
    public function broadcastOn(): Channel
    {
        return new Channel('conversation.' . $this->conversation->id);
    }

    /**
     * Flutter يستمع لـ:
     * message.sent
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Payload موحّد يخدم:
     * - شاشة الشات (message)
     * - شاشة قائمة المحادثات (conversation)
     *
     * ملاحظة مهمّة:
     * - unread_count هنا "ليس طرف واحد" لأنه Event واحد يخدم الطرفين.
     * - لذلك نرسل unread_for_teacher + unread_for_student + (اختياري) unread_count كأكبرهما للتوافق.
     */
    public function broadcastWith(): array
    {
        $sentAt = $this->message->sent_at
            ? $this->message->sent_at->toIso8601String()
            : optional($this->message->created_at)->toIso8601String();

        return [
            // للمحافظة على التوافق
            'conversation_id' => (int) $this->conversation->id,

            // ما يتوقعه Flutter: payload['message']
            'message' => [
                'id'              => (int) $this->message->id,
                'conversation_id' => (int) $this->message->conversation_id,
                'body'            => (string) $this->message->body,
                'sender_type'     => (string) $this->message->sender_type,
                'sender_id'       => (int) $this->message->sender_id,
                'sent_at'         => $sentAt,
                'read_at'         => optional($this->message->read_at)->toIso8601String(),
            ],

            // ما يتوقعه Flutter لتحديث ترتيب/المعاينة/العداد
            'conversation' => [
                'id'              => (int) $this->conversation->id,
                'last_message'    => (string) ($this->conversation->last_message ?? ''),
                'last_message_at' => optional($this->conversation->last_message_at)->toIso8601String(),

                // العدادات الصحيحة للطرفين
                'unread_for_teacher' => (int) $this->conversation->unread_for_teacher,
                'unread_for_student' => (int) $this->conversation->unread_for_student,

                // ✅ للتوافق مع كود Flutter (بعض الشاشات تقرأ unread_count)
                // بما أنه Event واحد للطرفين: نخليه max حتى لا يرجع 0 بالخطأ في أي طرف
                'unread_count' => (int) max(
                    (int) $this->conversation->unread_for_teacher,
                    (int) $this->conversation->unread_for_student
                ),
            ],
        ];
    }
}
