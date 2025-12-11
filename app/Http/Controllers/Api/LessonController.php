<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\LessonModule;
use App\Models\LessonTopic;
use App\Models\Teacher;
use App\Models\TeacherClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    /**
     * 🔹 حفظ أو تعديل درس (draft أو published)
     *
     * POST /api/teacher/lessons/save
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'teacher_code'     => 'required|string',
            'assignment_id'    => 'required|integer',
            'class_module_id' => 'nullable|integer',
            'class_section_id' => 'required|integer',
            'subject_id'       => 'required|integer',

            'lesson_id'        => 'nullable|integer',
            'title'            => 'required|string|max:255',
            'status'           => 'required|in:draft,published',

            // 🔹 الموديولات
            'modules'            => 'array',
            'modules.*.id'       => 'nullable', // مفتاح مؤقت من Flutter (مثل "m1")
            'modules.*.title'    => 'required|string|max:255',
            'modules.*.position' => 'integer',

            // 🔹 التوبيكس
            'topics'               => 'array',
            'topics.*.id'          => 'nullable',
            'topics.*.module_id'   => 'nullable', // مفتاح مؤقت لربط التوبيك بالموديول
            'topics.*.title'       => 'required|string|max:255',
            'topics.*.position'    => 'integer',

            // 🔹 البلوكات (بدون أي Subtopic)
            'blocks'                  => 'array',
            'blocks.*.id'             => 'nullable|integer',
            'blocks.*.type'           => 'required|in:text,image,video,audio',
            'blocks.*.body'           => 'nullable|string',
            'blocks.*.caption'        => 'nullable|string|max:255',
            'blocks.*.media_url'      => 'nullable|string',
            'blocks.*.media_path'     => 'nullable|string',
            'blocks.*.media_mime'     => 'nullable|string',
            'blocks.*.media_size'     => 'nullable|integer',
            'blocks.*.media_duration' => 'nullable|integer',
            'blocks.*.module_id'      => 'nullable', // مفتاح مؤقت مثل "m1"
            'blocks.*.topic_id'       => 'nullable',
            'blocks.*.position'       => 'integer',
            'blocks.*.meta'           => 'array',
        ]);

        // 🔍 التأكد من الأستاذ والإسناد
        $teacher = Teacher::where('teacher_code', $validated['teacher_code'])->first();
        if (! $teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found',
            ], 404);
        }

        $assignment = TeacherClassSubject::where('id', $validated['assignment_id'])
            ->where('teacher_id', $teacher->id)
            ->first();

        if (! $assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found for this teacher',
            ], 404);
        }

        $lesson = null;

        DB::connection('app_mysql')->transaction(function () use ($validated, $teacher, $assignment, &$lesson) {
            // 🔹 إنشاء أو تحديث الدرس
            if (! empty($validated['lesson_id'])) {
                $lesson = Lesson::on('app_mysql')->findOrFail($validated['lesson_id']);
            } else {
                $lesson = new Lesson();
                $lesson->setConnection('app_mysql');
            }

            $lesson->teacher_id       = $teacher->id;
            $lesson->assignment_id    = $assignment->id;
            $lesson->class_module_id = $validated['class_module_id'] ?? null;
            $lesson->class_section_id = $validated['class_section_id'];
            $lesson->subject_id       = $validated['subject_id'];
            $lesson->title            = $validated['title'];
            $lesson->status           = $validated['status'];

            if ($validated['status'] === 'published' && ! $lesson->published_at) {
                $lesson->published_at = now();
            }

            $lesson->save();

            // 🔄 إعادة بناء الموديولات + التوبيكس + البلوكات (بدون Subtopics)
            $lesson->modules()->delete();
            $lesson->topics()->delete();
            $lesson->blocks()->delete();

            // 🔹 الموديولات
            $modulesMap = [];
            foreach ($validated['modules'] ?? [] as $modData) {
                $mod = new LessonModule();
                $mod->setConnection('app_mysql');
                $mod->lesson_id = $lesson->id;
                $mod->title     = $modData['title'];
                $mod->position  = $modData['position'] ?? 0;
                $mod->save();

                // key مؤقت من Flutter → id الحقيقي
                $modulesMap[$modData['id'] ?? $modData['title']] = $mod->id;
            }

            // 🔹 التوبيكس
            $topicsMap = [];
            foreach ($validated['topics'] ?? [] as $topicData) {
                $topic = new LessonTopic();
                $topic->setConnection('app_mysql');
                $topic->lesson_id = $lesson->id;
                $topic->title     = $topicData['title'];
                $topic->position  = $topicData['position'] ?? 0;

                $key = $topicData['module_id'] ?? null; // مفتاح مؤقت مثل "m1"
                if ($key && isset($modulesMap[$key])) {
                    $topic->module_id = $modulesMap[$key];
                }

                $topic->save();
                $topicsMap[$topicData['id'] ?? $topicData['title']] = $topic->id;
            }

            // 🔹 البلوكات (Text / Image / Video / Audio)
            foreach ($validated['blocks'] ?? [] as $blockData) {
                $block = new LessonBlock();
                $block->setConnection('app_mysql');

                $block->lesson_id = $lesson->id;
                $block->type      = $blockData['type'];
                $block->body      = $blockData['body'] ?? null;
                $block->caption   = $blockData['caption'] ?? null;
                $block->media_path= $blockData['media_path'] ?? null;
                $block->media_url = $blockData['media_url'] ?? null;
                $block->media_mime= $blockData['media_mime'] ?? null;
                $block->media_size= $blockData['media_size'] ?? null;
                $block->media_duration = $blockData['media_duration'] ?? null;
                $block->position  = $blockData['position'] ?? 0;
                $block->meta      = $blockData['meta'] ?? null;

                $mKey = $blockData['module_id'] ?? null;
                if ($mKey && isset($modulesMap[$mKey])) {
                    $block->module_id = $modulesMap[$mKey];
                }

                $tKey = $blockData['topic_id'] ?? null;
                if ($tKey && isset($topicsMap[$tKey])) {
                    $block->topic_id = $topicsMap[$tKey];
                }

                $block->save();
            }
        });

        return response()->json([
            'success'   => true,
            'lesson_id' => $lesson->id,
            'status'    => $lesson->status,
        ]);
    }

    /**
     * 🔹 دروس الأستاذ (مع فلترة اختيارية)
     *
     * GET /api/teacher/lessons?teacher_code=XXX
     *    + اختياري:
     *      &assignment_id=..
     *      &class_section_id=..
     *      &subject_id=..
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'teacher_code'     => 'required|string',
            'assignment_id'    => 'nullable|integer',
            'class_section_id' => 'nullable|integer',
            'subject_id'       => 'nullable|integer',
        ]);

        $teacher = Teacher::where('teacher_code', $validated['teacher_code'])->firstOrFail();

        $query = Lesson::on('app_mysql')
            ->where('teacher_id', $teacher->id);

        if (! empty($validated['assignment_id'])) {
            $query->where('assignment_id', $validated['assignment_id']);
        }

        if (! empty($validated['class_section_id'])) {
            $query->where('class_section_id', $validated['class_section_id']);
        }

        if (! empty($validated['subject_id'])) {
            $query->where('subject_id', $validated['subject_id']);
        }

        $lessons = $query
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'lessons' => $lessons,
        ]);
    }

    /**
     * 🔹 جلب درس واحد مع الموديولات + التوبيكس + البلوكات
     *
     * GET /api/teacher/lessons/{lesson}
     */
    public function show(Lesson $lesson)
    {
        $lesson->setConnection('app_mysql');

        // بدون subtopics
        $lesson->load(['modules', 'topics', 'blocks']);

        return response()->json([
            'success' => true,
            'lesson'  => $lesson,
        ]);
    }

    /**
     * 🔹 حذف درس واحد (مع كل الموديولات والتوبيكس والبلوكات التابعة)
     *
     * DELETE /api/teacher/lessons/{lesson}
     */
    public function destroy(Lesson $lesson)
    {
        $lesson->setConnection('app_mysql');

        DB::connection('app_mysql')->transaction(function () use ($lesson) {
            $lesson->blocks()->delete();
            $lesson->topics()->delete();
            $lesson->modules()->delete();
            $lesson->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully',
        ]);
    }

    /**
     * 🔹 حذف مجموعة دروس (Bulk Delete)
     *
     * POST /api/teacher/lessons/bulk-delete
     * body: { lesson_ids: [1,2,3,...] }
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'lesson_ids'   => 'required|array',
            'lesson_ids.*' => 'integer',
        ]);

        DB::connection('app_mysql')->transaction(function () use ($validated) {
            $lessons = Lesson::on('app_mysql')
                ->whereIn('id', $validated['lesson_ids'])
                ->get();

            foreach ($lessons as $lesson) {
                $lesson->blocks()->delete();
                $lesson->topics()->delete();
                $lesson->modules()->delete();
                $lesson->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Lessons deleted successfully',
        ]);
    }
}
