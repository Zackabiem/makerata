<?php

namespace App\Http\Controllers;

use App\Models\TerminalRemark;
use App\Models\Student;
use App\Services\ReportCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TerminalRemarkController extends Controller
{
    protected ReportCardService $reportCardService;

    public function __construct(ReportCardService $reportCardService)
    {
        $this->reportCardService = $reportCardService;
    }

    /**
     * Get terminal remarks & dynamic suggestions for a student or class.
     */
    public function index(Request $request)
    {
        $schoolId  = $request->query('school_id', 1);
        $classId   = $request->query('class_id');
        $sessionId = $request->query('session_id', 1);
        $termId    = $request->query('term_id', 1);
        $studentId = $request->query('student_id');

        if ($studentId) {
            $remark = TerminalRemark::where('school_id', $schoolId)
                ->where('session_id', $sessionId)
                ->where('term_id', $termId)
                ->where('student_id', $studentId)
                ->first();

            // Calculate student average to generate dynamic remark suggestions
            $suggestions = $this->getDynamicRemarkSuggestions($schoolId, (int) $studentId, (int) $classId, (int) $sessionId, (int) $termId);

            if (!$remark) {
                return response()->json([
                    'status' => 'success',
                    'data'   => [
                        'school_id'          => $schoolId,
                        'student_id'         => (int) $studentId,
                        'class_id'           => (int) $classId,
                        'session_id'         => (int) $sessionId,
                        'term_id'            => (int) $termId,
                        'form_master_remark' => $suggestions['suggested_form_master_remark'],
                        'principal_remark'   => $suggestions['suggested_principal_remark'],
                        'promotion_status'   => $suggestions['suggested_promotion_status'],
                        'next_term_begins'   => '2026-09-15',
                        'suggestions'        => $suggestions,
                    ],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data'   => array_merge($remark->toArray(), [
                    'suggestions' => $suggestions,
                ]),
            ]);
        }

        $remarks = TerminalRemark::where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->get()
            ->keyBy('student_id');

        return response()->json([
            'status' => 'success',
            'data'   => $remarks,
        ]);
    }

    /**
     * Save terminal remarks for a student or batch.
     */
    public function batchSave(Request $request)
    {
        $validated = $request->validate([
            'school_id'  => 'required|integer',
            'class_id'   => 'required|integer',
            'session_id' => 'required|integer',
            'term_id'    => 'required|integer',
            'remarks'    => 'required|array',
            'remarks.*.student_id'         => 'required|integer',
            'remarks.*.form_master_remark' => 'nullable|string',
            'remarks.*.principal_remark'   => 'nullable|string',
            'remarks.*.promotion_status'   => 'nullable|string',
            'remarks.*.next_term_begins'   => 'nullable|string',
        ]);

        $schoolId  = $validated['school_id'];
        $classId   = $validated['class_id'];
        $sessionId = $validated['session_id'];
        $termId    = $validated['term_id'];

        DB::beginTransaction();
        try {
            foreach ($validated['remarks'] as $item) {
                TerminalRemark::updateOrCreate(
                    [
                        'school_id'  => $schoolId,
                        'student_id' => $item['student_id'],
                        'class_id'   => $classId,
                        'session_id' => $sessionId,
                        'term_id'    => $termId,
                    ],
                    [
                        'form_master_remark' => $item['form_master_remark'] ?? 'Good academic effort.',
                        'principal_remark'   => $item['principal_remark'] ?? 'Satisfactory progress made.',
                        'promotion_status'   => $item['promotion_status'] ?? 'Promoted',
                        'next_term_begins'   => $item['next_term_begins'] ?? '2026-09-15',
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Terminal remarks updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save terminal remarks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method: Generate dynamic remark suggestions based on student average.
     */
    protected function getDynamicRemarkSuggestions(int $schoolId, int $studentId, int $classId, int $sessionId, int $termId): array
    {
        $reportData = $this->reportCardService->compileTerminalReport($schoolId, $studentId, $classId, $sessionId, $termId);
        $avg = $reportData['overall_average'] ?? 0;

        if ($avg >= 80) {
            $formMaster = "An exceptional performance! Displays commendable dedication and intellectual excellence.";
            $principal  = "Outstanding overall achievement. Recommended for Academic Honor Roll.";
            $promo      = "Promoted with Distinction";
        } elseif ($avg >= 70) {
            $formMaster = "A very strong academic result. Demonstrates high competence across most subjects.";
            $principal  = "Very good overall result. Maintain this high level of enthusiasm next term.";
            $promo      = "Promoted";
        } elseif ($avg >= 60) {
            $formMaster = "Good academic performance with room for further refinement in key technical subjects.";
            $principal  = "Satisfactory performance. Encouraged to strive for higher honors next term.";
            $promo      = "Promoted";
        } elseif ($avg >= 50) {
            $formMaster = "Pass mark attained. Must devote more focus and study hours to strengthen weak areas.";
            $principal  = "Average result. Consistent revision and parental guidance recommended.";
            $promo      = "Promoted on Probation";
        } else {
            $formMaster = "Weak performance. Struggles with core competencies and requires close academic mentoring.";
            $principal  = "Unsatisfactory academic progress. Parental consultation required before next session.";
            $promo      = "Advised to Repeat";
        }

        return [
            'overall_average'               => $avg,
            'suggested_form_master_remark'  => $formMaster,
            'suggested_principal_remark'    => $principal,
            'suggested_promotion_status'    => $promo,
        ];
    }
}
