<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\Message;
<<<<<<< HEAD
use Illuminate\Broadcasting\Channel;
=======
use Illuminate\Broadcasting\PrivateChannel;
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
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
<<<<<<< HEAD
     * Flutter يشترك على:
     * conversation.{id}
=======
     * Client subscribes to:
     * private-conversation.{id}
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('conversation.' . $this->conversation->id);
    }

<<<<<<< HEAD
    /**
     * Flutter يستمع لـ:
     * message.sent
     */
=======
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

<<<<<<< HEAD
    /**
     * Payload موحّد يخدم:
     * - شاشة الشات (message)
     * - شاشة قائمة المحادثات (conversation)
     *
     * ملاحظة مهمّة:
     * - unread_count هنا "ليس طرف واحد" لأنه Event واحد يخدم الطرفين.
     * - لذلك نرسل unread_for_teacher + unread_for_student + (اختياري) unread_count كأكبرهما للتوافق.
     */
=======
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
    public function broadcastWith(): array
    {
        $sentAt = $this->message->sent_at
            ? $this->message->sent_at->toIso8601String()
            : optional($this->message->created_at)->toIso8601String();
<<<<<<< HEAD

        return [
            // للمحافظة على التوافق
            'conversation_id' => (int) $this->conversation->id,

            // ما يتوقعه Flutter: payload['message']
=======

        $lastAt = $this->conversation->last_message_at
            ? $this->conversation->last_message_at->toIso8601String()
            : optional($this->conversation->updated_at)->toIso8601String();

        return [
            'conversation_id' => (int) $this->conversation->id,
            'server_time'     => now()->toIso8601String(),

>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
            'message' => [
                'id'              => (int) $this->message->id,
                'conversation_id' => (int) $this->message->conversation_id,
                'body'            => (string) $this->message->body,
                'sender_type'     => (string) $this->message->sender_type,
                'sender_id'       => (int) $this->message->sender_id,
                'sent_at'         => $sentAt,
                'read_at'         => optional($this->message->read_at)->toIso8601String(),
            ],

<<<<<<< HEAD
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
=======
            'conversation' => [
                'id'                 => (int) $this->conversation->id,
                'last_message'       => (string) ($this->conversation->last_message ?? ''),
                'last_message_at'    => $lastAt,

                'unread_for_teacher' => (int) $this->conversation->unread_for_teacher,
                'unread_for_student' => (int) $this->conversation->unread_for_student,

                // Compatibility: event is shared for both sides
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
                'unread_count' => (int) max(
                    (int) $this->conversation->unread_for_teacher,
                    (int) $this->conversation->unread_for_student
                ),
            ],
        ];
    }
}
