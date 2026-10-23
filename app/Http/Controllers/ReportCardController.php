<?php

namespace App\Http\Controllers;

use App\Services\ReportCardService;
use App\Models\Student;
use App\Models\TerminalScoreSheet;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    protected ReportCardService $reportCardService;

    public function __construct(ReportCardService $reportCardService)
    {
        $this->reportCardService = $reportCardService;
    }

    /**
     * Get compiled terminal report card data (JSON or Blade View).
     */
    public function getTerminalReport(Request $request)
    {
        $request->validate([
            'student_id'   => 'nullable|integer',
            'admission_no' => 'nullable|string',
            'school_id'    => 'nullable|integer',
            'class_id'     => 'nullable|integer',
            'session_id'   => 'nullable|integer',
            'term_id'      => 'nullable|integer',
        ]);

        $studentId = $request->input('student_id');
        $admissionNo = $request->input('admission_no');
        $student = null;

        if ($studentId) {
            $student = Student::find((int)$studentId);
        } elseif ($admissionNo) {
            $student = Student::where('admission_no', trim($admissionNo))->first();
        }

        if (!$student) {
            if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Student record not found for the provided ID or Admission Number.',
                ], 404);
            }
            return back()->with('error', 'Student record not found.');
        }

        $studentId = $student->student_id;
        $schoolId = $request->input('school_id') ?? $student->school_id ?? 1;

        // Auto-detect class_id, session_id, term_id if not explicitly provided
        $latestScore = TerminalScoreSheet::where('student_id', $studentId)->latest()->first();

        $classId   = $request->input('class_id') ?? ($latestScore ? $latestScore->class_id : 1);
        $sessionId = $request->input('session_id') ?? ($latestScore ? $latestScore->session_id : 1);
        $termId    = $request->input('term_id') ?? ($latestScore ? $latestScore->term_id : 1);

        $data = $this->reportCardService->compileTerminalReport(
            (int)$schoolId,
            (int)$studentId,
            (int)$classId,
            (int)$sessionId,
            (int)$termId
        );

        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        }

        return view('reports.termly_report', $data);
    }

    /**
     * Get compiled annual report card data (JSON or Blade View).
     */
    public function getAnnualReport(Request $request)
    {
        $request->validate([
            'student_id'   => 'nullable|integer',
            'admission_no' => 'nullable|string',
            'school_id'    => 'nullable|integer',
            'class_id'     => 'nullable|integer',
            'session_id'   => 'nullable|integer',
        ]);

        $studentId = $request->input('student_id');
        $admissionNo = $request->input('admission_no');
        $student = null;

        if ($studentId) {
            $student = Student::find((int)$studentId);
        } elseif ($admissionNo) {
            $student = Student::where('admission_no', trim($admissionNo))->first();
        }

        if (!$student) {
            if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Student record not found for the provided ID or Admission Number.',
                ], 404);
            }
            return back()->with('error', 'Student record not found.');
        }

        $studentId = $student->student_id;
        $schoolId = $request->input('school_id') ?? $student->school_id ?? 1;

        $latestScore = TerminalScoreSheet::where('student_id', $studentId)->latest()->first();
        $classId   = $request->input('class_id') ?? ($latestScore ? $latestScore->class_id : 1);
        $sessionId = $request->input('session_id') ?? ($latestScore ? $latestScore->session_id : 1);

        $data = $this->reportCardService->compileAnnualReport(
            (int)$schoolId,
            (int)$studentId,
            (int)$classId,
            (int)$sessionId
        );

        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        }

        return view('reports.annual_report', $data);
    }
}
