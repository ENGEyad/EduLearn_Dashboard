<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Student;
use App\Models\Teacher;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    /**
     * فتح / إنشاء محادثة بين أستاذ وطالب
     * POST /api/chat/conversations/open
     */
    public function openConversation(Request $request)
    {
        $validated = $request->validate([
            'teacher_code'     => 'required|string',
            'academic_id'      => 'required|string',
            'class_section_id' => 'nullable|integer',
            'subject_id'       => 'nullable|integer',
        ]);

        $teacher = Teacher::where('teacher_code', $validated['teacher_code'])->first();
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.',
            ], 404);
        }

        $student = Student::where('academic_id', $validated['academic_id'])->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        $classSectionId = $validated['class_section_id'] ?? $student->class_section_id;
        $subjectId      = $validated['subject_id'] ?? null;

        $conversation = Conversation::firstOrCreate(
            [
                'teacher_id'       => $teacher->id,
                'student_id'       => $student->id,
                'class_section_id' => $classSectionId,
                'subject_id'       => $subjectId,
            ],
            [
                'last_message'        => null,
                'last_message_at'     => null,
                'unread_for_teacher'  => 0,
                'unread_for_student'  => 0,
            ]
        );

        return response()->json([
            'success'      => true,
            'conversation' => $this->formatConversation($conversation, forTeacher: true),
        ]);
    }

    /**
     * قائمة محادثات الأستاذ
     * GET /api/chat/conversations/teacher?teacher_code=...
     */
    public function teacherConversations(Request $request)
    {
        $request->validate([
            'teacher_code' => 'required|string',
        ]);

        $teacher = Teacher::where('teacher_code', $request->teacher_code)->first();
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.',
            ], 404);
        }

        // ترتيب حسب آخر رسالة (الأحدث أولاً) مع احتياط updated_at
        $conversations = Conversation::where('teacher_id', $teacher->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success'       => true,
            'conversations' => $conversations->map(
                fn (Conversation $c) => $this->formatConversation($c, forTeacher: true)
            ),
        ]);
    }

    /**
     * قائمة محادثات الطالب
     * GET /api/chat/conversations/student?academic_id=...
     */
    public function studentConversations(Request $request)
    {
        $request->validate([
            'academic_id' => 'required|string',
        ]);

        $student = Student::where('academic_id', $request->academic_id)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        $conversations = Conversation::where('student_id', $student->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success'       => true,
            'conversations' => $conversations->map(
                fn (Conversation $c) => $this->formatConversation($c, forTeacher: false)
            ),
        ]);
    }

    /**
     * جلب رسائل محادثة معيّنة
     * GET /api/chat/conversations/{conversation}/messages?as=teacher|student&teacher_code=..&academic_id=..
     */
    public function messages(Request $request, Conversation $conversation)
    {
        $request->validate([
            'as'           => 'required|in:teacher,student',
            'teacher_code' => 'nullable|string',
            'academic_id'  => 'nullable|string',
        ]);

        if ($request->as === 'teacher') {
            $teacher = Teacher::where('teacher_code', $request->teacher_code)->first();
            if (!$teacher || $teacher->id !== $conversation->teacher_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized teacher.',
                ], 403);
            }

            // ✅ نعلّم رسائل الطالب للأستاذ كمقروءة
            Message::where('conversation_id', $conversation->id)
                ->where('sender_type', 'student')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $conversation->update([
                'unread_for_teacher' => 0,
            ]);
        } else {
            $student = Student::where('academic_id', $request->academic_id)->first();
            if (!$student || $student->id !== $conversation->student_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized student.',
                ], 403);
            }

            // ✅ نعلّم رسائل الأستاذ للطالب كمقروءة
            Message::where('conversation_id', $conversation->id)
                ->where('sender_type', 'teacher')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $conversation->update([
                'unread_for_student' => 0,
            ]);
        }

        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success'  => true,
            'messages' => $messages->map(
                fn (Message $m) => $this->formatMessage($m)
            ),
        ]);
    }

    /**
     * إرسال رسالة جديدة
     * POST /api/chat/conversations/{conversation}/messages
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'sender_type'  => 'required|in:teacher,student',
            'teacher_code' => 'nullable|string',
            'academic_id'  => 'nullable|string',
            'body'         => 'required|string',
        ]);

        if ($validated['sender_type'] === 'teacher') {
            $teacher = Teacher::where('teacher_code', $validated['teacher_code'])->first();
            if (!$teacher || $teacher->id !== $conversation->teacher_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized teacher.',
                ], 403);
            }
            $senderId = $teacher->id;
        } else {
            $student = Student::where('academic_id', $validated['academic_id'])->first();
            if (!$student || $student->id !== $conversation->student_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized student.',
                ], 403);
            }
            $senderId = $student->id;
        }

        // نستخدم Carbon مباشرة لضمان الدقة
        $now = Carbon::now();

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => $validated['sender_type'],
            'sender_id'       => $senderId,
            'body'            => $validated['body'],
            'sent_at'         => $now,
        ]);

        // تحديث بيانات آخر رسالة + عدد غير المقروء
        $conversation->last_message    = $validated['body'];
        $conversation->last_message_at = $now;

        if ($validated['sender_type'] === 'teacher') {
            $conversation->unread_for_student = max(0, (int) $conversation->unread_for_student + 1);
        } else {
            $conversation->unread_for_teacher = max(0, (int) $conversation->unread_for_teacher + 1);
        }

        $conversation->save();
        $conversation->refresh(); // نتأكد أن الكائن محدث بالكامل

        // بث الحدث لــ Laravel Reverb / Pusher
        broadcast(new MessageSent($message, $conversation))->toOthers();

        return response()->json([
            'success'      => true,
            'message'      => $this->formatMessage($message),
            // ✅ نرجّع المحادثة المحدثة عشان تقدر من الفلاتر تحدث ترتيب القائمة فوراً
            'conversation' => $this->formatConversation(
                $conversation,
                forTeacher: $validated['sender_type'] === 'teacher'
            ),
        ]);
    }

    /**
     * تنسيق بيانات المحادثة للـ API
     * هنا نجيب الأستاذ والطالب يدويًا من قاعدة الداشبورد (الافتراضية)
     * بدون الاعتماد على علاقات Conversation اللي على app_mysql
     */
    protected function formatConversation(Conversation $c, bool $forTeacher = true): array
    {
        $teacher = $c->teacher_id ? Teacher::find($c->teacher_id) : null;
        $student = $c->student_id ? Student::find($c->student_id) : null;

        return [
            'id'               => $c->id,
            'teacher_id'       => $c->teacher_id,
            'student_id'       => $c->student_id,
            'class_section_id' => $c->class_section_id,
            'subject_id'       => $c->subject_id,
            'last_message'     => $c->last_message,
            // ✅ ISO8601 (يشتغل ممتاز مع DateTime.parse في Flutter)
            'last_message_at'  => optional($c->last_message_at)->toIso8601String(),

            // ✅ للواجهة: حسب من هو المستلم (أستاذ أو طالب)
            'unread_count'     => $forTeacher
                ? (int) $c->unread_for_teacher
                : (int) $c->unread_for_student,

            'teacher' => $teacher ? [
                'id'           => $teacher->id,
                'full_name'    => $teacher->full_name,
                'teacher_code' => $teacher->teacher_code,
                'image'        => $teacher->image ?? null,
            ] : null,

            'student' => $student ? [
                'id'            => $student->id,
                'full_name'     => $student->full_name,
                'academic_id'   => $student->academic_id,
                'image'         => $student->image ?? null,
                'grade'         => $student->grade,
                'class_section' => $student->class_section,
            ] : null,
        ];
    }

    /**
     * تنسيق بيانات الرسالة للـ API
     */
    protected function formatMessage(Message $m): array
    {
        return [
            'id'              => $m->id,
            'conversation_id' => $m->conversation_id,
            'sender_type'     => $m->sender_type,
            'sender_id'       => $m->sender_id,
            'body'            => $m->body,
            // ✅ يرجع بصيغة ISO8601 (Flutter ياخذها ويحوّلها ل HH:mm)
            'sent_at'         => optional($m->sent_at)->toIso8601String(),
            'read_at'         => optional($m->read_at)->toIso8601String(),
        ];
    }
}
