<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

/**
<<<<<<< HEAD
 * Channel name must match Flutter:
 * conversation.{id}
 *
 * ملاحظة: Flutter/Reverb عندك حالياً بدون Auth (Channel عادي)،
 * لذلك نُرجّع true مؤقتاً. لاحقاً يمكن تحويلها إلى PrivateChannel
 * وتطبيق تحقق حقيقي حسب المستخدم.
 */
Broadcast::channel('conversation.{conversationId}', function ($user = null, $conversationId) {
    // ✅ حماية بسيطة: لا تسمح لقناة لمحادثة غير موجودة
    return Conversation::whereKey($conversationId)->exists();
=======
 * Private channel authorization for:
 * private-conversation.{conversationId}
 *
 * In Laravel, you define it as:
 * conversation.{conversationId}
 * and the client subscribes to:
 * private-conversation.{conversationId}
 */
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        return false;
    }

    // We expect $user to be injected by our custom BroadcastAuthController
    $role = $user->role ?? null;

    if ($role === 'teacher') {
        return (int) $conversation->teacher_id === (int) $user->id;
    }

    if ($role === 'student') {
        return (int) $conversation->student_id === (int) $user->id;
    }

    return false;
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
});
