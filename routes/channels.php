<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user = null, $conversationId) {
    // مؤقتاً نسمح للجميع، بعدين ممكن نقيّدها
    return true;
});
