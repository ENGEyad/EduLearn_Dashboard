<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        return view('teachers', [
            'pageTitle' => 'Teachers',
            'pageSubtitle' => 'Add, edit and monitor teachers',
            'TEACHERS_ROUTES' => [
                'list'    => route('teachers.list'),
                'store'   => route('teachers.store'),
                // نخلي __ID__ عشان الـ JS يستبدلها
                'update'  => route('teachers.update', ['teacher' => '__ID__']),
                'destroy' => route('teachers.destroy', ['teacher' => '__ID__']),
                'import'  => route('teachers.import'),
            ],
        ]);
    }

    public function list()
    {
        return response()->json(Teacher::orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $this->sanitize($request);

        // كود تلقائي لو ما وصل
        if (empty($data['teacher_code'])) {
            $data['teacher_code'] = $this->generateTeacherCode();
        }

        $teacher = Teacher::create($data);

        return response()->json($teacher, 201);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $this->sanitize($request);
        $teacher->update($data);
        return response()->json($teacher);
    }

    public function destroy(Teacher $teacher)
    {
      $teacher->delete();
      return response()->json(['deleted' => true]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            if (!$row) continue;
            $rowData = array_combine($header, $row);
            if (empty($rowData['full_name'])) continue;

            Teacher::create([
                'full_name'    => $rowData['full_name'],
                'teacher_code' => $rowData['teacher_code'] ?? $this->generateTeacherCode(),
                'email'        => $rowData['email'] ?? null,
                'phone'        => $rowData['phone'] ?? null,
                'status'       => $rowData['status'] ?? 'Active',
            ]);
        }

        return response()->json(['message' => 'Teachers imported successfully']);
    }

    /** أهم جزء: تنظيف البيانات القادمة من الـ JS */
    private function sanitize(Request $request): array
    {
        // ناخذها كلها
        $data = $request->all();

        // 1) لو مافي اسم نوقف
        if (empty($data['full_name'])) {
            abort(422, 'full_name is required');
        }

        // 2) الحقول اللي ممكن تكون فاضية نخليها null
        $nullable = [
            'email','phone','birth_governorate','birthdate','qualification','qualification_date',
            'current_school','join_date','current_role','shift','national_id','marital_status',
            'district','neighborhood','street','stage','experience_place','status'
        ];
        foreach ($nullable as $key) {
            if (array_key_exists($key, $data) && $data[$key] === '') {
                $data[$key] = null;
            }
        }

        // 3) الحقول الرقمية
        $ints = ['age','weekly_load','salary','children','students_count','experience_years'];
        foreach ($ints as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = ($data[$key] === '' || $data[$key] === null) ? null : (int) $data[$key];
            }
        }

        // 4) الحقول العشرية
        $floats = ['avg_student_score','attendance_rate'];
        foreach ($floats as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = ($data[$key] === '' || $data[$key] === null) ? null : (float) $data[$key];
            }
        }

        // 5) المصفوفات
        $data['subjects'] = $request->input('subjects', []);
        $data['grades']   = $request->input('grades', []);

        return $data;
    }

    protected function generateTeacherCode(): string
    {
        $year = now()->year;
        $rand = rand(100, 999);
        return "T-{$year}-{$rand}";
    }
}
