<?php

namespace App\Http\Controllers;

use App\Models\GradingScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradingSystemController extends Controller
{
    /**
     * Get configured grading scales for a school.
     */
    public function index(Request $request)
    {
        $schoolId = $request->query('school_id', 1);

        $scales = GradingScale::where('school_id', $schoolId)
            ->orderBy('min_score', 'desc')
            ->get();

        // If no scales found, populate standard defaults
        if ($scales->isEmpty()) {
            $defaultScales = [
                ['grade' => 'A', 'min_score' => 70, 'max_score' => 100, 'remark' => 'EXCELLENT'],
                ['grade' => 'B', 'min_score' => 60, 'max_score' => 69,  'remark' => 'VERY GOOD'],
                ['grade' => 'C', 'min_score' => 50, 'max_score' => 59,  'remark' => 'GOOD'],
                ['grade' => 'D', 'min_score' => 45, 'max_score' => 49,  'remark' => 'PASS'],
                ['grade' => 'E', 'min_score' => 40, 'max_score' => 44,  'remark' => 'FAIR'],
                ['grade' => 'F', 'min_score' => 0,  'max_score' => 39,  'remark' => 'FAIL'],
            ];

            foreach ($defaultScales as $def) {
                GradingScale::create(array_merge($def, ['school_id' => $schoolId]));
            }

            $scales = GradingScale::where('school_id', $schoolId)
                ->orderBy('min_score', 'desc')
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data'   => $scales,
        ]);
    }

    /**
     * Store a single grade scale entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|integer',
            'grade'     => 'required|string|max:10',
            'min_score' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|numeric|min:0|max:100',
            'remark'    => 'required|string|max:100',
        ]);

        $scale = GradingScale::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Grading scale rule created.',
            'data'    => $scale,
        ], 210);
    }

    /**
     * Update an existing grade scale entry.
     */
    public function update(Request $request, $id)
    {
        $scale = GradingScale::find($id);

        if (!$scale) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Grading scale rule not found.',
            ], 404);
        }

        $validated = $request->validate([
            'grade'     => 'sometimes|required|string|max:10',
            'min_score' => 'sometimes|required|numeric|min:0|max:100',
            'max_score' => 'sometimes|required|numeric|min:0|max:100',
            'remark'    => 'sometimes|required|string|max:100',
        ]);

        $scale->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Grading scale rule updated.',
            'data'    => $scale,
        ]);
    }

    /**
     * Replace all grading scale rules for a school in bulk.
     */
    public function batchSave(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|integer',
            'scales'    => 'required|array',
            'scales.*.grade'     => 'required|string|max:10',
            'scales.*.min_score' => 'required|numeric|min:0|max:100',
            'scales.*.max_score' => 'required|numeric|min:0|max:100',
            'scales.*.remark'    => 'required|string|max:100',
        ]);

        $schoolId = $validated['school_id'];

        DB::beginTransaction();
        try {
            GradingScale::where('school_id', $schoolId)->delete();

            foreach ($validated['scales'] as $item) {
                GradingScale::create([
                    'school_id' => $schoolId,
                    'grade'     => $item['grade'],
                    'min_score' => $item['min_score'],
                    'max_score' => $item['max_score'],
                    'remark'    => strtoupper($item['remark']),
                ]);
            }

            DB::commit();

            $updatedScales = GradingScale::where('school_id', $schoolId)
                ->orderBy('min_score', 'desc')
                ->get();

            return response()->json([
                'status'  => 'success',
                'message' => 'Grading scale system updated successfully.',
                'data'    => $updatedScales,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update grading system: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a grade scale entry.
     */
    public function destroy($id)
    {
        $scale = GradingScale::find($id);

        if (!$scale) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Grading scale rule not found.',
            ], 404);
        }

        $scale->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Grading scale rule deleted.',
        ]);
    }
}
