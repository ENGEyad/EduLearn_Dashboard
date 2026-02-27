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
use Illuminate\Support\Facades\DB;

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
            'as'               => 'nullable|in:teacher,student',
        ]);

<<<<<<< HEAD
        // ✅ تحديد المنظور (افتراضي: teacher لو لم يُرسل)
=======
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
        $as = $validated['as'] ?? 'teacher';
        $forTeacher = $as === 'teacher';

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

        // ملاحظة: نُبقي المنطق كما هو (بدون تغيير DB)
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

        // تحميل الطرفين مرة واحدة لتفادي تكرار الاستعلامات
        [$teacherMap, $studentMap] = $this->loadActorsForConversations(collect([$conversation]));

        return response()->json([
            'success'      => true,
<<<<<<< HEAD
            // ✅ الآن unread_count صحيح حسب الطرف
            'conversation' => $this->formatConversation($conversation, $forTeacher),
=======
            'conversation' => $this->formatConversation($conversation, $forTeacher, $teacherMap, $studentMap),
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
        ]);
    }

    /**
     * قائمة محادثات الأستاذ
     * GET /api/chat/conversations/teacher?teacher_code=...
     */
    public function teacherConversations(Request $request)
    {
        $validated = $request->validate([
            'teacher_code' => 'required|string',
        ]);

        $teacher = Teacher::where('teacher_code', $validated['teacher_code'])->first();
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.',
            ], 404);
        }

        $conversations = Conversation::where('teacher_id', $teacher->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        [$teacherMap, $studentMap] = $this->loadActorsForConversations($conversations);

        return response()->json([
            'success'       => true,
            'conversations' => $conversations->map(
<<<<<<< HEAD
                fn (Conversation $c) => $this->formatConversation($c, true)
=======
                fn (Conversation $c) => $this->formatConversation($c, true, $teacherMap, $studentMap)
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
            ),
        ]);
    }

    /**
     * قائمة محادثات الطالب
     * GET /api/chat/conversations/student?academic_id=...
     */
    public function studentConversations(Request $request)
    {
        $validated = $request->validate([
            'academic_id' => 'required|string',
        ]);

        $student = Student::where('academic_id', $validated['academic_id'])->first();
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

        [$teacherMap, $studentMap] = $this->loadActorsForConversations($conversations);

        return response()->json([
            'success'       => true,
            'conversations' => $conversations->map(
<<<<<<< HEAD
                fn (Conversation $c) => $this->formatConversation($c, false)
=======
                fn (Conversation $c) => $this->formatConversation($c, false, $teacherMap, $studentMap)
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
            ),
        ]);
    }

    /**
     * جلب رسائل محادثة معيّنة
     * GET /api/chat/conversations/{conversation}/messages?as=teacher|student
<<<<<<< HEAD
=======
     *
     * تحسين UX بدون كسر: إذا أرسلت limit يرجّع صفحة + next_cursor
     * - limit (اختياري): عدد الرسائل (مثلاً 30)
     * - before_id (اختياري): لجلب رسائل أقدم من هذا الـ id
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
     */
    public function messages(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'as'           => 'required|in:teacher,student',
            'teacher_code' => 'nullable|string',
            'academic_id'  => 'nullable|string',

            'limit'        => 'nullable|integer|min:1|max:200',
            'before_id'    => 'nullable|integer|min:1',
        ]);

        // تحقق صلاحية الطرف على المحادثة + جلب actor
        $actor = $this->resolveAndAuthorizeActor($validated, $conversation);

        // تعليم الرسائل كمقروءة (حسب الطرف)
        DB::transaction(function () use ($validated, $conversation) {
            if ($validated['as'] === 'teacher') {
                $updated = Message::where('conversation_id', $conversation->id)
                    ->where('sender_type', 'student')
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);

                if ($updated > 0) {
                    $conversation->update(['unread_for_teacher' => 0]);
                }
            } else {
                $updated = Message::where('conversation_id', $conversation->id)
                    ->where('sender_type', 'teacher')
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);

                if ($updated > 0) {
                    $conversation->update(['unread_for_student' => 0]);
                }
            }
        });

        // الاستعلام الأساسي
        $query = Message::where('conversation_id', $conversation->id)->orderBy('created_at');

        // Pagination اختياري (بدون كسر القديم)
        $limit = $validated['limit'] ?? null;
        $beforeId = $validated['before_id'] ?? null;

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        if ($limit) {
            // نجيب الأقدم/الأحدث حسب ترتيبك الحالي (created_at ASC).
            // بما أن ASC، "before_id" يعني أقدم، فسنحافظ على ASC ونأخذ limit من النهاية؟
            // لتبسيط وعدم كسر: سنغيّر على id DESC ثم نعكس النتائج.
            $paged = Message::where('conversation_id', $conversation->id)
                ->when($beforeId, fn ($q) => $q->where('id', '<', $beforeId))
                ->orderByDesc('id')
                ->limit($limit + 1)
                ->get();

            $hasMore = $paged->count() > $limit;
            if ($hasMore) {
                $paged = $paged->take($limit);
            }

<<<<<<< HEAD
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

            Message::where('conversation_id', $conversation->id)
                ->where('sender_type', 'teacher')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $conversation->update([
                'unread_for_student' => 0,
=======
            $messages = $paged->reverse()->values();
            $nextCursor = $hasMore ? $messages->first()->id : null;

            return response()->json([
                'success'     => true,
                'messages'    => $messages->map(fn (Message $m) => $this->formatMessage($m)),
                'next_cursor' => $nextCursor, // استخدمه كـ before_id لجلب الأقدم
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
            ]);
        }

        // السلوك القديم: كل الرسائل
        $messages = $query->get();

        return response()->json([
            'success'  => true,
            'messages' => $messages->map(fn (Message $m) => $this->formatMessage($m)),
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
            'body'         => 'required|string|min:1|max:5000',
        ]);

        // تحقق صلاحية المرسل على المحادثة + sender_id
        $sender = $this->resolveAndAuthorizeSender($validated, $conversation);
        $senderId = $sender['id'];

        $now = Carbon::now();

        // Transaction لضمان عدم حصول inconsistency
        $result = DB::transaction(function () use ($validated, $conversation, $senderId, $now) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_type'     => $validated['sender_type'],
                'sender_id'       => $senderId,
                'body'            => $validated['body'],
                'sent_at'         => $now,
            ]);

<<<<<<< HEAD
        $conversation->last_message    = $validated['body'];
        $conversation->last_message_at = $now;

        if ($validated['sender_type'] === 'teacher') {
            $conversation->unread_for_student++;
        } else {
            $conversation->unread_for_teacher++;
        }

        $conversation->save();
        $conversation->refresh();

        broadcast(new MessageSent($message, $conversation))->toOthers();
=======
            // تحديث المحادثة
            $conversation->last_message    = $validated['body'];
            $conversation->last_message_at = $now;

            if ($validated['sender_type'] === 'teacher') {
                $conversation->unread_for_student = (int) $conversation->unread_for_student + 1;
            } else {
                $conversation->unread_for_teacher = (int) $conversation->unread_for_teacher + 1;
            }

            $conversation->save();
            $conversation->refresh();

            return [$message, $conversation];
        });

        /** @var Message $message */
        /** @var Conversation $freshConversation */
        [$message, $freshConversation] = $result;

        // بثّ الرسالة للطرف الآخر (للوصول الفوري)
        broadcast(new MessageSent($message, $freshConversation))->toOthers();

        // تجهيز خرائط الطرفين للمحادثة
        [$teacherMap, $studentMap] = $this->loadActorsForConversations(collect([$freshConversation]));
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719

        return response()->json([
            'success'      => true,
            'message'      => $this->formatMessage($message),
            'conversation' => $this->formatConversation(
<<<<<<< HEAD
                $conversation,
                $validated['sender_type'] === 'teacher'
=======
                $freshConversation,
                $validated['sender_type'] === 'teacher',
                $teacherMap,
                $studentMap
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
            ),
        ]);
    }

    /**
<<<<<<< HEAD
     * تنسيق بيانات المحادثة
=======
     * تحميل بيانات المعلمين والطلاب لمجموعة محادثات مرة واحدة لتفادي N+1
     * @return array{0: array<int,Teacher>, 1: array<int,Student>}
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
     */
    protected function loadActorsForConversations($conversations): array
    {
        $teacherIds = $conversations->pluck('teacher_id')->filter()->unique()->values();
        $studentIds = $conversations->pluck('student_id')->filter()->unique()->values();

        $teachers = Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');
        $students = Student::whereIn('id', $studentIds)->get()->keyBy('id');

        return [$teachers->all(), $students->all()];
    }

    /**
     * توحيد التحقق من هوية الطرف الذي يطلب الرسائل (as)
     * يعيد الكائن (Teacher/Student) أو يرمي 403
     */
    protected function resolveAndAuthorizeActor(array $validated, Conversation $conversation)
    {
        if (($validated['as'] ?? null) === 'teacher') {
            $teacherCode = $validated['teacher_code'] ?? null;
            $teacher = $teacherCode ? Teacher::where('teacher_code', $teacherCode)->first() : null;

            if (!$teacher || $teacher->id !== $conversation->teacher_id) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Unauthorized teacher.',
                ], 403));
            }

            return $teacher;
        }

        $academicId = $validated['academic_id'] ?? null;
        $student = $academicId ? Student::where('academic_id', $academicId)->first() : null;

        if (!$student || $student->id !== $conversation->student_id) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthorized student.',
            ], 403));
        }

        return $student;
    }

    /**
     * توحيد التحقق من هوية المرسل عند الإرسال
     * @return array{id:int,type:string}
     */
    protected function resolveAndAuthorizeSender(array $validated, Conversation $conversation): array
    {
        if ($validated['sender_type'] === 'teacher') {
            $teacherCode = $validated['teacher_code'] ?? null;
            $teacher = $teacherCode ? Teacher::where('teacher_code', $teacherCode)->first() : null;

            if (!$teacher || $teacher->id !== $conversation->teacher_id) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Unauthorized teacher.',
                ], 403));
            }

            return ['id' => $teacher->id, 'type' => 'teacher'];
        }

        $academicId = $validated['academic_id'] ?? null;
        $student = $academicId ? Student::where('academic_id', $academicId)->first() : null;

        if (!$student || $student->id !== $conversation->student_id) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthorized student.',
            ], 403));
        }

        return ['id' => $student->id, 'type' => 'student'];
    }

    /**
     * تنسيق بيانات المحادثة
     */
    protected function formatConversation(
        Conversation $c,
        bool $forTeacher = true,
        array $teacherMap = [],
        array $studentMap = []
    ): array {
        $teacher = $c->teacher_id ? ($teacherMap[$c->teacher_id] ?? null) : null;
        $student = $c->student_id ? ($studentMap[$c->student_id] ?? null) : null;

        return [
            'id'               => $c->id,
            'teacher_id'       => $c->teacher_id,
            'student_id'       => $c->student_id,
            'class_section_id' => $c->class_section_id,
            'subject_id'       => $c->subject_id,
            'last_message'     => $c->last_message,
            'last_message_at'  => optional($c->last_message_at)->toIso8601String(),

<<<<<<< HEAD
            // ✅ العدّاد الصحيح حسب الطرف
=======
>>>>>>> f192308a22e7ff4ac1048e5218d7e67a3bcc4719
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
     * تنسيق بيانات الرسالة
     */
    protected function formatMessage(Message $m): array
    {
        return [
            'id'              => $m->id,
            'conversation_id' => $m->conversation_id,
            'sender_type'     => $m->sender_type,
            'sender_id'       => $m->sender_id,
            'body'            => $m->body,
            'sent_at'         => optional($m->sent_at)->toIso8601String(),
            'read_at'         => optional($m->read_at)->toIso8601String(),
        ];
    }
}
