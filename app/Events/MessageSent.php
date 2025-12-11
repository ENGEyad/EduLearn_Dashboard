<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Conversation;
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
     * اسم القناة التي يشترك فيها تطبيق Flutter:
     * conversation.{id}
     */
    public function broadcastOn(): Channel
    {
        return new Channel('conversation.' . $this->conversation->id);
    }

    /**
     * اسم الحدث الذي يستمع له Flutter: message.sent
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * البيانات المرسلة عبر الـ WebSocket
     */
    public function broadcastWith(): array
    {
        return [
            // نفس المفتاح القديم لو كنت تستعمله في أي مكان آخر
            'conversation_id' => $this->conversation->id,

            // ما يتوقعه كود Flutter: payload['message'] ...
            'message' => [
                'id'              => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'body'            => $this->message->body,
                'sender_type'     => $this->message->sender_type,
                'sender_id'       => $this->message->sender_id,
                'sent_at'         => optional($this->message->sent_at)->toIso8601String(),
                'read_at'         => optional($this->message->read_at)->toIso8601String(),
            ],

            // معلومات المحادثة لو حبيت تحدّث قائمة المحادثات في الواجهة
            'conversation' => [
                'id'                 => $this->conversation->id,
                'last_message'       => $this->conversation->last_message,
                'last_message_at'    => optional($this->conversation->last_message_at)->toIso8601String(),
                'unread_for_teacher' => (int) $this->conversation->unread_for_teacher,
                'unread_for_student' => (int) $this->conversation->unread_for_student,
            ],
        ];
    }
}
