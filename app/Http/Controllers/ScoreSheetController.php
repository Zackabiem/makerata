<?php

namespace App\Http\Controllers;

use App\Models\TerminalScoreSheet;
use App\Models\GradingScale;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreSheetController extends Controller
{
    /**
     * Fetch score sheet for a given class, subject, session, and term.
     */
    public function getScores(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|integer',
            'class_id'  => 'required|integer',
            'subject_id'=> 'required|integer',
            'session_id'=> 'required|integer',
            'term_id'   => 'required|integer',
        ]);

        $schoolId  = $validated['school_id'];
        $classId   = $validated['class_id'];
        $subjectId = $validated['subject_id'];
        $sessionId = $validated['session_id'];
        $termId    = $validated['term_id'];

        // Get all active students in the class
        $students = Student::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('full_name', 'asc')
            ->get();

        // Get existing score entries
        $existingScores = TerminalScoreSheet::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->get()
            ->keyBy('student_id');

        $scoresList = $students->map(function ($student) use ($existingScores) {
            $score = $existingScores->get($student->id);
            return [
                'student_id'   => $student->id,
                'full_name'    => $student->full_name,
                'admission_no' => $student->admission_no,
                'first_test'   => $score ? $score->first_test : null,
                'second_test'  => $score ? $score->second_test : null,
                'third_test'   => $score ? $score->third_test : null,
                'exam_score'   => $score ? $score->exam_score : null,
                'total_score'  => $score ? $score->total_score : 0,
                'grade'        => $score ? $score->grade : '-',
                'remark'       => $score ? $score->remark : '-',
                'subject_rank' => $score ? $score->subject_rank : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $scoresList,
        ]);
    }

    /**
     * Batch save terminal score entries from Teacher Dashboard score sheet.
     */
    public function batchSave(Request $request)
    {
        $validated = $request->validate([
            'school_id'  => 'required|integer',
            'class_id'   => 'required|integer',
            'subject_id' => 'required|integer',
            'session_id' => 'required|integer',
            'term_id'    => 'required|integer',
            'scores'     => 'required|array',
            'scores.*.student_id'  => 'required|integer',
            'scores.*.first_test'  => 'nullable',
            'scores.*.second_test' => 'nullable',
            'scores.*.third_test'  => 'nullable',
            'scores.*.exam_score'  => 'nullable',
        ]);

        $schoolId  = $validated['school_id'];
        $classId   = $validated['class_id'];
        $subjectId = $validated['subject_id'];
        $sessionId = $validated['session_id'];
        $termId    = $validated['term_id'];

        DB::beginTransaction();
        try {
            foreach ($validated['scores'] as $item) {
                $parseVal = function($v) {
                    if ($v === null || $v === '') return null;
                    return is_numeric($v) ? (float)$v : null;
                };

                $t1   = $parseVal($item['first_test'] ?? null);
                $t2   = $parseVal($item['second_test'] ?? null);
                $t3   = $parseVal($item['third_test'] ?? null);
                $exam = $parseVal($item['exam_score'] ?? null);

                $total = ($t1 ?? 0) + ($t2 ?? 0) + ($t3 ?? 0) + ($exam ?? 0);
                $scale = GradingScale::resolveScale($total, $schoolId);

                TerminalScoreSheet::updateOrCreate(
                    [
                        'student_id' => $item['student_id'],
                        'subject_id' => $subjectId,
                        'term_id'    => $termId,
                    ],
                    [
                        'school_id'   => $schoolId,
                        'class_id'    => $classId,
                        'session_id'  => $sessionId,
                        'first_test'  => $t1,
                        'second_test' => $t2,
                        'third_test'  => $t3,
                        'exam_score'  => $exam,
                        'total_score' => $total,
                        'grade'       => $scale['grade'],
                        'remark'      => $scale['remark'],
                    ]
                );
            }

            // Calculate subject positions/ranks for this class and subject
            $allScores = TerminalScoreSheet::where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('session_id', $sessionId)
                ->where('term_id', $termId)
                ->orderBy('total_score', 'desc')
                ->get();

            $rank = 1;
            foreach ($allScores as $index => $scoreRecord) {
                if ($index > 0 && $scoreRecord->total_score < $allScores[$index - 1]->total_score) {
                    $rank = $index + 1;
                }
                $scoreRecord->subject_rank = $rank;
                $scoreRecord->save();
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Scores saved and subject ranks computed successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save scores: ' . $e->getMessage(),
            ], 500);
        }
    }
}
