<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\Api\TeacherAuthController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\LessonMediaController;
use App\Http\Controllers\Api\StudentLessonController;
use App\Http\Controllers\Api\ClassModuleController;
use App\Http\Controllers\Api\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ✅ مسار الطالب
Route::post('/student/auth', [StudentAuthController::class, 'auth']);

// ✅ مسار الأستاذ
Route::post('/teacher/auth', [TeacherAuthController::class, 'auth']);

// ==================== دروس الأستاذ ====================
Route::post('/teacher/lessons/save', [LessonController::class, 'save']);
Route::get('/teacher/lessons', [LessonController::class, 'index']);
Route::get('/teacher/lessons/{lesson}', [LessonController::class, 'show']);
Route::delete('/teacher/lessons/{lesson}', [LessonController::class, 'destroy']);
Route::post('/teacher/lessons/bulk-delete', [LessonController::class, 'bulkDelete']);

Route::post('/teacher/lessons/media', [LessonMediaController::class, 'store']);

// ==================== موديولات الفصل (Class Modules) ====================
Route::get('/teacher/class-modules', [ClassModuleController::class, 'index']);
Route::post('/teacher/class-modules', [ClassModuleController::class, 'store']);
Route::put('/teacher/class-modules/{module}', [ClassModuleController::class, 'update']);
Route::delete('/teacher/class-modules/{module}', [ClassModuleController::class, 'destroy']);
Route::get('/teacher/class-modules/{module}/lessons', [ClassModuleController::class, 'lessons']);

// ==================== دروس الطالب ====================
Route::get('/student/lessons', [StudentLessonController::class, 'index']);
Route::post('/student/lessons/update-status', [StudentLessonController::class, 'updateStatus']);

// ==================== دردشة الأستاذ / الطالب ====================

// فتح / إنشاء محادثة بين أستاذ وطالب
Route::post('/chat/conversations/open', [ChatController::class, 'openConversation']);

// قائمة محادثات الأستاذ
Route::get('/chat/conversations/teacher', [ChatController::class, 'teacherConversations']);

// قائمة محادثات الطالب
Route::get('/chat/conversations/student', [ChatController::class, 'studentConversations']);

// رسائل محادثة معيّنة
Route::get('/chat/conversations/{conversation}/messages', [ChatController::class, 'messages']);

// إرسال رسالة في محادثة معيّنة
Route::post('/chat/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
