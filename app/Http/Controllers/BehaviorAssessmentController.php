<?php

namespace App\Http\Controllers;

use App\Models\BehaviorAssessment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BehaviorAssessmentController extends Controller
{
    /**
     * Get behavior assessment records for a class or student.
     */
    public function index(Request $request)
    {
        $schoolId  = $request->query('school_id', 1);
        $classId   = $request->query('class_id');
        $sessionId = $request->query('session_id', 1);
        $termId    = $request->query('term_id', 1);
        $studentId = $request->query('student_id');

        $query = BehaviorAssessment::with('student')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($studentId) {
            $query->where('student_id', $studentId);
            $assessment = $query->first();

            if (!$assessment) {
                // Return default/blank rating structure if not saved yet
                return response()->json([
                    'status' => 'success',
                    'data'   => [
                        'school_id'           => $schoolId,
                        'student_id'          => (int) $studentId,
                        'class_id'            => (int) $classId,
                        'session_id'          => (int) $sessionId,
                        'term_id'             => (int) $termId,
                        'punctuality'         => 4,
                        'neatness'            => 4,
                        'politeness'          => 4,
                        'honesty'             => 4,
                        'cooperation'         => 4,
                        'leadership'          => 3,
                        'emotional_stability' => 4,
                        'attitude_to_work'    => 4,
                        'handwriting'         => 3,
                        'verbal_fluency'      => 4,
                        'sports'              => 3,
                        'crafts'              => 3,
                        'drawing'             => 3,
                        'musical_skills'      => 3,
                    ],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $assessment,
            ]);
        }

        $assessments = $query->get()->keyBy('student_id');

        return response()->json([
            'status' => 'success',
            'data'   => $assessments,
        ]);
    }

    /**
     * Batch save behavior assessments for class students.
     */
    public function batchSave(Request $request)
    {
        $validated = $request->validate([
            'school_id'    => 'required|integer',
            'class_id'     => 'required|integer',
            'session_id'   => 'required|integer',
            'term_id'      => 'required|integer',
            'assessments'  => 'required|array',
            'assessments.*.student_id'          => 'required|integer',
            'assessments.*.punctuality'         => 'nullable|integer|between:1,5',
            'assessments.*.neatness'            => 'nullable|integer|between:1,5',
            'assessments.*.politeness'          => 'nullable|integer|between:1,5',
            'assessments.*.honesty'             => 'nullable|integer|between:1,5',
            'assessments.*.cooperation'         => 'nullable|integer|between:1,5',
            'assessments.*.leadership'          => 'nullable|integer|between:1,5',
            'assessments.*.emotional_stability' => 'nullable|integer|between:1,5',
            'assessments.*.attitude_to_work'    => 'nullable|integer|between:1,5',
            'assessments.*.handwriting'         => 'nullable|integer|between:1,5',
            'assessments.*.verbal_fluency'      => 'nullable|integer|between:1,5',
            'assessments.*.sports'              => 'nullable|integer|between:1,5',
            'assessments.*.crafts'              => 'nullable|integer|between:1,5',
            'assessments.*.drawing'             => 'nullable|integer|between:1,5',
            'assessments.*.musical_skills'      => 'nullable|integer|between:1,5',
        ]);

        $schoolId  = $validated['school_id'];
        $classId   = $validated['class_id'];
        $sessionId = $validated['session_id'];
        $termId    = $validated['term_id'];

        DB::beginTransaction();
        try {
            foreach ($validated['assessments'] as $item) {
                BehaviorAssessment::updateOrCreate(
                    [
                        'school_id'  => $schoolId,
                        'student_id' => $item['student_id'],
                        'class_id'   => $classId,
                        'session_id' => $sessionId,
                        'term_id'    => $termId,
                    ],
                    [
                        'punctuality'         => $item['punctuality'] ?? 4,
                        'neatness'            => $item['neatness'] ?? 4,
                        'politeness'          => $item['politeness'] ?? 4,
                        'honesty'             => $item['honesty'] ?? 4,
                        'cooperation'         => $item['cooperation'] ?? 4,
                        'leadership'          => $item['leadership'] ?? 3,
                        'emotional_stability' => $item['emotional_stability'] ?? 4,
                        'attitude_to_work'    => $item['attitude_to_work'] ?? 4,
                        'handwriting'         => $item['handwriting'] ?? 3,
                        'verbal_fluency'      => $item['verbal_fluency'] ?? 4,
                        'sports'              => $item['sports'] ?? 3,
                        'crafts'              => $item['crafts'] ?? 3,
                        'drawing'             => $item['drawing'] ?? 3,
                        'musical_skills'      => $item['musical_skills'] ?? 3,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Behavior & skills assessments saved successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save behavior assessment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
