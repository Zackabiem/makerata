<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use Illuminate\Http\Request;

class HomeworkController extends Controller
{
    /**
     * Get homework assignments list for class/teacher/student.
     */
    public function index(Request $request)
    {
        $schoolId  = $request->query('school_id', 1);
        $classId   = $request->query('class_id');
        $subjectId = $request->query('subject_id');
        $staffId   = $request->query('staff_id');

        $query = Homework::with(['classroom', 'subject', 'teacher'])
            ->where('school_id', $schoolId);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        $assignments = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $assignments,
        ]);
    }

    /**
     * Create a new homework assignment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'   => 'required|integer',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'class_id'    => 'required|integer',
            'subject_id'  => 'required|integer',
            'staff_id'    => 'required|integer',
            'due_date'    => 'nullable|date',
            'max_score'   => 'nullable|numeric|min:1',
            'file_url'    => 'nullable|string',
            'file_name'   => 'nullable|string',
        ]);

        $homework = Homework::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Homework created successfully.',
            'data'    => $homework->load(['classroom', 'subject', 'teacher']),
        ], 210);
    }

    /**
     * Submit homework response by student.
     */
    public function submitHomework(Request $request, $id)
    {
        $homework = Homework::find($id);

        if (!$homework) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Homework assignment not found.',
            ], 404);
        }

        $validated = $request->validate([
            'student_id'      => 'required|integer',
            'submission_text' => 'nullable|string',
            'file_url'        => 'nullable|string',
            'file_name'       => 'nullable|string',
        ]);

        $submission = HomeworkSubmission::updateOrCreate(
            [
                'homework_id' => $id,
                'student_id'  => $validated['student_id'],
            ],
            [
                'submission_text' => $validated['submission_text'] ?? null,
                'file_url'        => $validated['file_url'] ?? null,
                'file_name'       => $validated['file_name'] ?? null,
                'status'          => 'submitted',
                'submitted_at'    => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Homework submitted successfully.',
            'data'    => $submission,
        ]);
    }

    /**
     * Grade homework submission.
     */
    public function gradeSubmission(Request $request, $submissionId)
    {
        $submission = HomeworkSubmission::find($submissionId);

        if (!$submission) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Homework submission not found.',
            ], 404);
        }

        $validated = $request->validate([
            'score'   => 'required|numeric|min:0',
            'comment' => 'nullable|string',
        ]);

        $submission->update([
            'score'   => $validated['score'],
            'comment' => $validated['comment'] ?? null,
            'status'  => 'graded',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Submission graded successfully.',
            'data'    => $submission,
        ]);
    }
}
