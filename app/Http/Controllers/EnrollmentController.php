<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    /**
     * Enroll single student into a class arm.
     */
    public function enrollStudent(Request $request)
    {
        $validated = $request->validate([
            'school_id'  => 'required|integer',
            'student_id' => 'required|integer|exists:students,student_id',
            'class_id'   => 'required|integer|exists:classrooms,class_id',
            'session_id' => 'required|integer',
            'term_id'    => 'required|integer',
            'status'     => 'nullable|string|in:active,promoted,repeated,withdrawn,graduated',
        ]);

        $student = Student::find($validated['student_id']);

        $enrollment = Enrollment::updateOrCreate(
            [
                'school_id'  => $validated['school_id'],
                'student_id' => $validated['student_id'],
                'session_id' => $validated['session_id'],
                'term_id'    => $validated['term_id'],
            ],
            [
                'class_id' => $validated['class_id'],
                'status'   => $validated['status'] ?? 'active',
            ]
        );

        // Update student's primary active class_id
        $student->class_id = $validated['class_id'];
        $student->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Student enrolled in class successfully.',
            'data'    => $enrollment->load(['student', 'classroom']),
        ]);
    }

    /**
     * Bulk enroll students into a class.
     */
    public function bulkEnroll(Request $request)
    {
        $validated = $request->validate([
            'school_id'   => 'required|integer',
            'class_id'    => 'required|integer|exists:classrooms,class_id',
            'session_id'  => 'required|integer',
            'term_id'     => 'required|integer',
            'student_ids' => 'required|array',
            'student_ids.*' => 'integer|exists:students,student_id',
        ]);

        DB::beginTransaction();
        try {
            $enrolled = [];
            foreach ($validated['student_ids'] as $sId) {
                $enrollment = Enrollment::updateOrCreate(
                    [
                        'school_id'  => $validated['school_id'],
                        'student_id' => $sId,
                        'session_id' => $validated['session_id'],
                        'term_id'    => $validated['term_id'],
                    ],
                    [
                        'class_id' => $validated['class_id'],
                        'status'   => 'active',
                    ]
                );

                // Update active student class reference
                Student::where('student_id', $sId)->update(['class_id' => $validated['class_id']]);
                $enrolled[] = $enrollment;
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => count($enrolled) . ' students enrolled into target class successfully.',
                'data'    => $enrolled,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Bulk enrollment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch promotion of students from source class to target class.
     */
    public function promoteStudents(Request $request)
    {
        $validated = $request->validate([
            'school_id'        => 'required|integer',
            'from_class_id'    => 'required|integer|exists:classrooms,class_id',
            'to_class_id'      => 'required|integer|exists:classrooms,class_id',
            'new_session_id'   => 'required|integer',
            'new_term_id'      => 'required|integer',
            'student_ids'      => 'required|array',
            'student_ids.*'    => 'integer|exists:students,student_id',
        ]);

        DB::beginTransaction();
        try {
            $promotedCount = 0;
            foreach ($validated['student_ids'] as $sId) {
                // Record promotion
                Enrollment::updateOrCreate(
                    [
                        'school_id'  => $validated['school_id'],
                        'student_id' => $sId,
                        'session_id' => $validated['new_session_id'],
                        'term_id'    => $validated['new_term_id'],
                    ],
                    [
                        'class_id' => $validated['to_class_id'],
                        'status'   => 'promoted',
                    ]
                );

                Student::where('student_id', $sId)->update(['class_id' => $validated['to_class_id']]);
                $promotedCount++;
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "{$promotedCount} students successfully promoted to new class.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Promotion failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student enrollment history across academic terms.
     */
    public function getHistory(Request $request, $studentId)
    {
        $enrollments = Enrollment::with(['classroom', 'school'])
            ->where('student_id', $studentId)
            ->orderBy('session_id', 'desc')
            ->orderBy('term_id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $enrollments,
        ]);
    }
}
