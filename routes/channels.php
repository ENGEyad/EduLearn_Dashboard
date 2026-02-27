<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

/**
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
});
