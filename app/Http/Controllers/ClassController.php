<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Staff;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    /**
     * List all classes with form masters and student counts.
     */
    public function index(Request $request)
    {
        $schoolId = $request->query('school_id', 1);

        $classes = Classroom::with(['formMaster', 'subjects'])
            ->withCount('students')
            ->where('school_id', $schoolId)
            ->orderBy('level', 'asc')
            ->orderBy('arm', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $classes,
        ]);
    }

    /**
     * Store a new class & arm.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'      => 'required|integer',
            'name'           => 'required|string|max:100',
            'level'          => 'required|string|max:50',
            'arm'            => 'nullable|string|max:50',
            'form_master_id' => 'nullable|integer|exists:staff,staff_id',
        ]);

        $classroom = Classroom::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Classroom created successfully.',
            'data'    => $classroom->load('formMaster'),
        ], 210);
    }

    /**
     * Display classroom details with students & subjects.
     */
    public function show($id)
    {
        $classroom = Classroom::with(['formMaster', 'students', 'subjects'])->find($id);

        if (!$classroom) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Classroom not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $classroom,
        ]);
    }

    /**
     * Update classroom configuration.
     */
    public function update(Request $request, $id)
    {
        $classroom = Classroom::find($id);

        if (!$classroom) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Classroom not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:100',
            'level'          => 'sometimes|required|string|max:50',
            'arm'            => 'nullable|string|max:50',
            'form_master_id' => 'nullable|integer|exists:staff,staff_id',
        ]);

        $classroom->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Classroom updated successfully.',
            'data'    => $classroom->fresh(['formMaster']),
        ]);
    }

    /**
     * Delete classroom.
     */
    public function destroy($id)
    {
        $classroom = Classroom::find($id);

        if (!$classroom) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Classroom not found.',
            ], 404);
        }

        $classroom->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Classroom deleted successfully.',
        ]);
    }

    /**
     * Assign Form Master (Teacher) to class.
     */
    public function assignFormMaster(Request $request, $id)
    {
        $classroom = Classroom::find($id);

        if (!$classroom) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Classroom not found.',
            ], 404);
        }

        $validated = $request->validate([
            'form_master_id' => 'required|integer|exists:staff,staff_id',
        ]);

        $classroom->form_master_id = $validated['form_master_id'];
        $classroom->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Form Master assigned successfully.',
            'data'    => $classroom->load('formMaster'),
        ]);
    }

    /**
     * Map curriculum subjects to classroom.
     */
    public function mapSubjects(Request $request, $id)
    {
        $classroom = Classroom::find($id);

        if (!$classroom) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Classroom not found.',
            ], 404);
        }

        $validated = $request->validate([
            'subject_ids'   => 'required|array',
            'subject_ids.*' => 'integer|exists:subjects,subject_id',
        ]);

        $classroom->subjects()->sync($validated['subject_ids']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Curriculum subjects mapped to class successfully.',
            'data'    => $classroom->fresh('subjects'),
        ]);
    }
}
