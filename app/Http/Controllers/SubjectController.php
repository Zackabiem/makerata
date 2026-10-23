<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * List all subjects for a school.
     */
    public function index(Request $request)
    {
        $schoolId = $request->query('school_id', 1);

        $subjects = Subject::with('classes')
            ->where('school_id', $schoolId)
            ->orderBy('category', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $subjects,
        ]);
    }

    /**
     * Store a new subject.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'   => 'required|integer',
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:20',
            'category'    => 'nullable|string|in:Core,Elective,Science,Arts,Commercial,General',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Subject created successfully.',
            'data'    => $subject,
        ], 210);
    }

    /**
     * Display a specific subject.
     */
    public function show($id)
    {
        $subject = Subject::with('classes')->find($id);

        if (!$subject) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Subject not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $subject,
        ]);
    }

    /**
     * Update subject attributes.
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Subject not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:100',
            'code'        => 'sometimes|required|string|max:20',
            'category'    => 'nullable|string|in:Core,Elective,Science,Arts,Commercial,General',
            'description' => 'nullable|string',
        ]);

        $subject->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Subject updated successfully.',
            'data'    => $subject,
        ]);
    }

    /**
     * Delete a subject.
     */
    public function destroy($id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Subject not found.',
            ], 404);
        }

        $subject->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Subject deleted successfully.',
        ]);
    }
}
