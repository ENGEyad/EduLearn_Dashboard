<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentLessonProgress;
use Illuminate\Http\Request;

class StudentLessonController extends Controller
{
    /**
     * 🔹 جلب دروس مادة معيّنة لطالب معيّن
     *
     * GET /api/student/lessons?academic_id=12345&subject_id=10
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'academic_id' => 'required|string',
            'subject_id'  => 'required|integer',
        ]);

        // 🧑‍🎓 الطالب من قاعدة edulearn_db الافتراضية
        $student = Student::where('academic_id', $validated['academic_id'])->first();

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        // نفترض أن لدى الطالب class_section_id
        $classSectionId = $student->class_section_id;

        // 🔹 الدروس المنشورة في هذه الشعبة + المادة من app_mysql
        // ✅ نحمّل علاقة classModule عشان نقدر نرجّع اسم الوحدة مع كل درس
        $lessons = Lesson::on('app_mysql')
            ->with('classModule') // ← مهم عشان نجيب عنوان الموديول
            ->where('class_section_id', $classSectionId)
            ->where('subject_id', $validated['subject_id'])
            ->where('status', 'published')
            // (اختياري) ترتيب حسب الموديول أولاً ثم تاريخ النشر
            ->orderBy('class_module_id')
            ->orderBy('published_at', 'asc')
            ->get();

        // 🔹 تقدّم الطالب في هذه الدروس
        $progress = StudentLessonProgress::on('app_mysql')
            ->where('student_id', $student->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->get()
            ->keyBy('lesson_id');

        $responseLessons = $lessons->values()->map(function (Lesson $lesson, $index) use ($progress) {
            $p = $progress->get($lesson->id);
            $status = $p ? $p->status : 'not_started'; // not_started | draft | completed

            return [
                'id'             => $lesson->id,
                'title'          => $lesson->title,
                'duration_label' => $lesson->meta['duration_label'] ?? null,
                'status'         => $status,
                'number'         => $index + 1,

                // ✅ إضافات جديدة ضرورية لواجهة الطالب:
                'class_module_id' => $lesson->class_module_id,
                'module_title'    => optional($lesson->classModule)->title ?? 'Lessons',
            ];
        });

        return response()->json([
            'success' => true,
            'lessons' => $responseLessons,
        ]);
    }

    /**
     * 🔹 تحديث حالة الدرس للطالب (draft / completed)
     *
     * POST /api/student/lessons/update-status
     * body: { academic_id, lesson_id, status }
     */
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'academic_id' => 'required|string',
            'lesson_id'   => 'required|integer',
            'status'      => 'required|in:draft,completed',
        ]);

        // الطالب
        $student = Student::where('academic_id', $validated['academic_id'])->first();

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        // الدرس من app_mysql
        $lesson = Lesson::on('app_mysql')->find($validated['lesson_id']);
        if (! $lesson) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson not found',
            ], 404);
        }

        $progress = StudentLessonProgress::on('app_mysql')->updateOrCreate(
            [
                'lesson_id' => $lesson->id,
                'student_id' => $student->id,
            ],
            [
                'status'         => $validated['status'],
                'last_opened_at' => now(),
                'completed_at'   => $validated['status'] === 'completed' ? now() : null,
            ]
        );

        return response()->json([
            'success' => true,
            'status'  => $progress->status,
        ]);
    }
}
