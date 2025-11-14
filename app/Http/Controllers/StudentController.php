<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * عرض صفحة الطلاب (الواجهة)
     */
    public function index()
    {
        return view('students', [
            'title'        => 'Students – EduLearn',
            'pageTitle'    => 'Student Management',
            'pageSubtitle' => 'Manage school students, status and profiles',
        ]);
    }

    /**
     * إرجاع قائمة الطلاب JSON للـ JS
     */
    public function list()
    {
        return Student::orderBy('id', 'desc')->get();
    }

    /**
     * توليد رقم أكاديمي فريد
     */
    protected function generateAcademicId(): string
    {
        do {
            $id = 'S-' . now()->year . '-' . strtoupper(Str::random(4));
        } while (Student::where('academic_id', $id)->exists());

        return $id;
    }

    /**
     * تخزين طالب جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'              => 'required|string|max:255',
            'gender'                 => 'nullable|string|max:20',
            'birthdate'              => 'nullable|date',
            'status'                 => 'nullable|string|max:50',
            'email'                  => 'nullable|email|max:255',
            'grade'                  => 'nullable|string|max:100',
            'class_section'          => 'nullable|string|max:50',
            'address_governorate'    => 'nullable|string|max:100',
            'address_city'           => 'nullable|string|max:100',
            'address_street'         => 'nullable|string|max:150',
            'guardian_name'          => 'nullable|string|max:255',
            'guardian_relation'      => 'nullable|string|max:100',
            'guardian_relation_other'=> 'nullable|string|max:100',
            'guardian_phone'         => 'nullable|string|max:50',
            'performance_avg'        => 'nullable|numeric|min:0|max:100',
            'attendance_rate'        => 'nullable|numeric|min:0|max:100',
            'notes'                  => 'nullable|string|max:500',
        ]);

        $student = new Student();
        $student->full_name              = $validated['full_name'];
        $student->gender                 = $validated['gender'] ?? null;
        $student->birthdate              = $validated['birthdate'] ?? null;
        $student->status                 = $validated['status'] ?? 'Active';
        $student->email                  = $validated['email'] ?? null;
        $student->grade                  = $validated['grade'] ?? null;
        $student->class_section          = $validated['class_section'] ?? null;
        $student->address_governorate    = $validated['address_governorate'] ?? null;
        $student->address_city           = $validated['address_city'] ?? null;
        $student->address_street         = $validated['address_street'] ?? null;
        $student->guardian_name          = $validated['guardian_name'] ?? null;
        $student->guardian_relation      = $validated['guardian_relation'] ?? null;
        $student->guardian_relation_other= $validated['guardian_relation_other'] ?? null;
        $student->guardian_phone         = $validated['guardian_phone'] ?? null;
        $student->performance_avg        = $validated['performance_avg'] ?? null;
        $student->attendance_rate        = $validated['attendance_rate'] ?? null;
        $student->notes                  = $validated['notes'] ?? null;

        // توليد رقم أكاديمي
        $student->academic_id = $this->generateAcademicId();

        $student->save();

        return response()->json($student, 201);
    }

    /**
     * تحديث طالب موجود
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'full_name'              => 'required|string|max:255',
            'gender'                 => 'nullable|string|max:20',
            'birthdate'              => 'nullable|date',
            'status'                 => 'nullable|string|max:50',
            'email'                  => 'nullable|email|max:255',
            'grade'                  => 'nullable|string|max:100',
            'class_section'          => 'nullable|string|max:50',
            'address_governorate'    => 'nullable|string|max:100',
            'address_city'           => 'nullable|string|max:100',
            'address_street'         => 'nullable|string|max:150',
            'guardian_name'          => 'nullable|string|max:255',
            'guardian_relation'      => 'nullable|string|max:100',
            'guardian_relation_other'=> 'nullable|string|max:100',
            'guardian_phone'         => 'nullable|string|max:50',
            'performance_avg'        => 'nullable|numeric|min:0|max:100',
            'attendance_rate'        => 'nullable|numeric|min:0|max:100',
            'notes'                  => 'nullable|string|max:500',
        ]);

        $student->full_name              = $validated['full_name'];
        $student->gender                 = $validated['gender'] ?? null;
        $student->birthdate              = $validated['birthdate'] ?? null;
        $student->status                 = $validated['status'] ?? 'Active';
        $student->email                  = $validated['email'] ?? null;
        $student->grade                  = $validated['grade'] ?? null;
        $student->class_section          = $validated['class_section'] ?? null;
        $student->address_governorate    = $validated['address_governorate'] ?? null;
        $student->address_city           = $validated['address_city'] ?? null;
        $student->address_street         = $validated['address_street'] ?? null;
        $student->guardian_name          = $validated['guardian_name'] ?? null;
        $student->guardian_relation      = $validated['guardian_relation'] ?? null;
        $student->guardian_relation_other= $validated['guardian_relation_other'] ?? null;
        $student->guardian_phone         = $validated['guardian_phone'] ?? null;
        $student->performance_avg        = $validated['performance_avg'] ?? null;
        $student->attendance_rate        = $validated['attendance_rate'] ?? null;
        $student->notes                  = $validated['notes'] ?? null;

        $student->save();

        return response()->json($student);
    }

    /**
     * حذف طالب
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully',
        ]);
    }

    /**
     * استيراد طلاب من CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return response()->json(['message' => 'Cannot open file'], 422);
        }

        $headers = fgetcsv($handle, 0, ',');
        if (!$headers) {
            return response()->json(['message' => 'Empty file'], 422);
        }

        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $data = array_combine($headers, $row);

                if (!isset($data['full_name']) || $data['full_name'] === '') {
                    continue;
                }

                $student = new Student();
                $student->full_name              = $data['full_name'] ?? '';
                $student->gender                 = $data['gender'] ?? null;
                $student->birthdate              = $data['birthdate'] ?? null;
                $student->status                 = $data['status'] ?? 'Active';
                $student->email                  = $data['email'] ?? null;
                $student->grade                  = $data['grade'] ?? null;
                $student->class_section          = $data['class_section'] ?? null;
                $student->address_governorate    = $data['address_governorate'] ?? null;
                $student->address_city           = $data['address_city'] ?? null;
                $student->address_street         = $data['address_street'] ?? null;
                $student->guardian_name          = $data['guardian_name'] ?? null;
                $student->guardian_relation      = $data['guardian_relation'] ?? null;
                $student->guardian_relation_other= $data['guardian_relation_other'] ?? null;
                $student->guardian_phone         = $data['guardian_phone'] ?? null;
                $student->performance_avg        = $data['performance_avg'] ?? null;
                $student->attendance_rate        = $data['attendance_rate'] ?? null;
                $student->notes                  = $data['notes'] ?? null;

                $student->academic_id = $this->generateAcademicId();

                $student->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Import failed',
                'error'   => $e->getMessage(),
            ], 500);
        } finally {
            fclose($handle);
        }

        return response()->json([
            'message' => 'Students imported successfully',
        ]);
    }
}
