<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Display admission register & student directory.
     */
    public function index(Request $request)
    {
        $schoolId = $request->query('school_id', 1);
        $classId  = $request->query('class_id');
        $search   = $request->query('search');

        $query = Student::with(['classroom', 'user'])
            ->where('school_id', $schoolId);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('admission_no', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('surname', 'asc')->orderBy('first_name', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $students,
        ]);
    }

    /**
     * Create new student entry in Admission Register.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'    => 'required|integer',
            'class_id'     => 'nullable|integer|exists:classrooms,class_id',
            'admission_no' => 'required|string|max:50|unique:students,admission_no',
            'surname'      => 'required|string|max:100',
            'first_name'   => 'required|string|max:100',
            'other_name'   => 'nullable|string|max:100',
            'gender'       => 'required|string|in:Male,Female,Other',
            'date_of_birth'=> 'nullable|date',
            'parent_name'  => 'required|string|max:150',
            'parent_phone' => 'required|string|max:30',
            'parent_email' => 'nullable|email|max:150',
            'passport_url' => 'nullable|string',
        ]);

        $validated['is_active'] = true;

        $student = Student::create($validated);

        // Auto-create initial enrollment record if class_id provided
        if (!empty($validated['class_id'])) {
            Enrollment::create([
                'school_id'  => $validated['school_id'],
                'student_id' => $student->student_id,
                'class_id'   => $validated['class_id'],
                'session_id' => $request->input('session_id', 1),
                'term_id'    => $request->input('term_id', 1),
                'status'     => 'active',
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Student admitted successfully.',
            'data'    => $student->load('classroom'),
        ], 210);
    }

    /**
     * Display student details.
     */
    public function show($id)
    {
        $student = Student::with(['classroom', 'user', 'enrollments.classroom'])->find($id);

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Student record not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $student,
        ]);
    }

    /**
     * Update student details.
     */
    public function update(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Student record not found.',
            ], 404);
        }

        $validated = $request->validate([
            'class_id'     => 'nullable|integer|exists:classrooms,class_id',
            'admission_no' => 'sometimes|required|string|max:50|unique:students,admission_no,' . $id . ',student_id',
            'surname'      => 'sometimes|required|string|max:100',
            'first_name'   => 'sometimes|required|string|max:100',
            'other_name'   => 'nullable|string|max:100',
            'gender'       => 'sometimes|required|string|in:Male,Female,Other',
            'date_of_birth'=> 'nullable|date',
            'parent_name'  => 'sometimes|required|string|max:150',
            'parent_phone' => 'sometimes|required|string|max:30',
            'parent_email' => 'nullable|email|max:150',
            'passport_url' => 'nullable|string',
            'is_active'    => 'boolean',
        ]);

        $student->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Student profile updated successfully.',
            'data'    => $student->fresh(['classroom', 'user']),
        ]);
    }

    /**
     * Delete student.
     */
    public function destroy($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Student record not found.',
            ], 404);
        }

        $student->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Student record deleted successfully.',
        ]);
    }

    /**
     * Bulk Import Students into Admission Register.
     */
    public function bulkImport(Request $request)
    {
        $validated = $request->validate([
            'school_id'  => 'required|integer',
            'students'   => 'required|array',
            'students.*.admission_no' => 'required|string',
            'students.*.surname'      => 'required|string',
            'students.*.first_name'   => 'required|string',
            'students.*.parent_name'  => 'required|string',
            'students.*.parent_phone' => 'required|string',
            'students.*.class_id'     => 'nullable|integer',
        ]);

        $created = [];
        foreach ($validated['students'] as $item) {
            $student = Student::updateOrCreate(
                [
                    'school_id'    => $validated['school_id'],
                    'admission_no' => $item['admission_no'],
                ],
                [
                    'surname'      => $item['surname'],
                    'first_name'   => $item['first_name'],
                    'other_name'   => $item['other_name'] ?? null,
                    'gender'       => $item['gender'] ?? 'Male',
                    'parent_name'  => $item['parent_name'],
                    'parent_phone' => $item['parent_phone'],
                    'class_id'     => $item['class_id'] ?? null,
                    'is_active'    => true,
                ]
            );
            $created[] = $student;
        }

        return response()->json([
            'status'  => 'success',
            'message' => count($created) . ' student records imported/updated.',
            'data'    => $created,
        ]);
    }
}
