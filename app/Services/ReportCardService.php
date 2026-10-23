<?php

namespace App\Services;

use App\Models\TerminalScoreSheet;
use App\Models\Student;
use App\Models\GradingScale;
use App\Models\BehaviorAssessment;
use App\Models\AttendanceRecord;
use App\Models\TerminalRemark;
use App\Services\RankingService;
use Illuminate\Support\Facades\DB;

class ReportCardService
{
    protected RankingService $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    /**
     * Compile Terminal Report Card live with dynamic subject stats & positions.
     */
    public function compileTerminalReport(int $schoolId, int $studentId, int $classId, int $sessionId, int $termId): array
    {
        // 1. Fetch student scores for this term
        $studentScores = TerminalScoreSheet::with('subject')
            ->where('student_id', $studentId)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->get();

        // 2. Fetch all scores in the class to compute subject statistics live (Average, Highest, Lowest, Subject Position)
        $allClassScores = TerminalScoreSheet::where('class_id', $classId)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->get()
            ->groupBy('subject_id');

        // 3. Compute class student totals for class position ranking
        $classStudentTotals = [];
        $classScoresByStudent = TerminalScoreSheet::where('class_id', $classId)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->get()
            ->groupBy('student_id');

        foreach ($classScoresByStudent as $sId => $scores) {
            $classStudentTotals[$sId] = $scores->sum('computed_total');
        }

        $classPositions = $this->rankingService->calculatePositions($classStudentTotals);

        // 4. Format individual subject rows with live statistics
        $subjectRows = [];
        foreach ($studentScores as $score) {
            $subjId = $score->subject_id;
            $subjScores = $allClassScores->get($subjId, collect([]));

            $highest = $subjScores->max('computed_total') ?? 0;
            $lowest = $subjScores->min('computed_total') ?? 0;
            $average = $subjScores->count() > 0 ? round($subjScores->avg('computed_total'), 1) : 0;

            // Subject position rankings
            $subjTotals = [];
            foreach ($subjScores as $sc) {
                $subjTotals[$sc->student_id] = $sc->computed_total;
            }
            $subjRanks = $this->rankingService->calculatePositions($subjTotals);

            $scale = GradingScale::resolveScale($score->computed_total, $schoolId);

            $subjectName = $score->subject ? $score->subject->name : ($score->subject_name ?? 'Subject ' . $score->subject_id);

            $subjectRows[] = [
                'subject_id'       => $score->subject_id,
                'subject_name'     => $subjectName,
                'first_test'       => $score->first_test,
                'second_test'      => $score->second_test,
                'third_test'       => $score->third_test,
                'exam_score'       => $score->exam_score,
                'total_score'      => $score->computed_total,
                'grade'            => $scale['grade'],
                'remark'           => $scale['remark'],
                'highest_score'    => $highest,
                'lowest_score'     => $lowest,
                'subject_average'  => $average,
                'subject_position' => $subjRanks[$studentId] ?? '-',
            ];
        }

        $grandTotal = $studentScores->sum('computed_total');
        $subjectCount = $studentScores->count();
        $overallAverage = $subjectCount > 0 ? round($grandTotal / $subjectCount, 2) : 0;

        // Fetch Behavior Assessment, Attendance & Remarks
        $behavior = BehaviorAssessment::where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->where('student_id', $studentId)
            ->first();

        $attendance = AttendanceRecord::where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->where('student_id', $studentId)
            ->first();

        $remark = TerminalRemark::where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->where('student_id', $studentId)
            ->first();

        return [
            'student'                 => Student::with('classroom')->find($studentId),
            'subjects'                => $subjectRows,
            'grand_total'             => $grandTotal,
            'overall_average'         => $overallAverage,
            'class_position'          => $classPositions[$studentId] ?? '-',
            'total_students_in_class' => count($classStudentTotals),
            'behavior_assessment'     => $behavior ?? [
                'punctuality' => 4, 'neatness' => 4, 'politeness' => 4, 'honesty' => 4,
                'cooperation' => 4, 'leadership' => 3, 'emotional_stability' => 4, 'attitude_to_work' => 4,
                'handwriting' => 3, 'verbal_fluency' => 4, 'sports' => 3, 'crafts' => 3,
                'drawing' => 3, 'musical_skills' => 3,
            ],
            'attendance_record'       => $attendance ?? [
                'days_school_opened' => 115,
                'days_present'       => 110,
                'days_absent'        => 5,
                'remarks'            => 'Punctual & regular',
            ],
            'terminal_remark'         => $remark ?? [
                'form_master_remark' => $overallAverage >= 70 ? 'Very impressive academic effort. Keep it up!' : 'Good performance with room for improvement.',
                'principal_remark'   => $overallAverage >= 70 ? 'Promoted with commendation.' : 'Satisfactory progress made.',
                'promotion_status'   => $overallAverage >= 50 ? 'Promoted' : 'On Probation',
                'next_term_begins'   => '2026-09-15',
            ],
        ];
    }

    /**
     * Compile Annual Summary Academic Report (Combining Terms 1, 2, and 3).
     */
    public function compileAnnualReport(int $schoolId, int $studentId, int $classId, int $sessionId): array
    {
        // 1. Fetch scores across all 3 terms for this session
        $allSessionScores = TerminalScoreSheet::with('subject')
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->get()
            ->groupBy('subject_id');

        // 2. Fetch all student scores in class for class annual statistics
        $allClassSessionScores = TerminalScoreSheet::where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->get()
            ->groupBy(['subject_id', 'student_id']);

        // Calculate student annual totals in class for position ranking
        $classStudentAnnualTotals = [];
        $scoresGroupedByStudent = TerminalScoreSheet::where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->get()
            ->groupBy('student_id');

        foreach ($scoresGroupedByStudent as $sId => $scores) {
            $classStudentAnnualTotals[$sId] = $scores->sum('computed_total');
        }

        $annualClassPositions = $this->rankingService->calculatePositions($classStudentAnnualTotals);

        // 3. Build Subject Annual Summary Rows
        $annualSubjectRows = [];
        $annualGrandTotal = 0;

        foreach ($allSessionScores as $subjectId => $scores) {
            $term1 = $scores->where('term_id', 1)->first();
            $term2 = $scores->where('term_id', 2)->first();
            $term3 = $scores->where('term_id', 3)->first();

            $t1Score = $term1 ? $term1->computed_total : 0;
            $t2Score = $term2 ? $term2->computed_total : 0;
            $t3Score = $term3 ? $term3->computed_total : 0;

            $yearlyTotal = $t1Score + $t2Score + $t3Score;
            $annualGrandTotal += $yearlyTotal;

            $termsTaken = count(array_filter([$t1Score, $t2Score, $t3Score], fn($v) => $v > 0)) ?: 1;
            $annualAvg = round($yearlyTotal / $termsTaken, 1);

            $scale = GradingScale::resolveScale($annualAvg, $schoolId);

            $firstScore = $scores->first();
            $subjectName = $firstScore->subject ? $firstScore->subject->name : 'Subject ' . $subjectId;

            // Class highest/lowest for subject yearly total
            $subjectClassTotals = [];
            if ($allClassSessionScores->has($subjectId)) {
                foreach ($allClassSessionScores->get($subjectId) as $sId => $stScores) {
                    $subjectClassTotals[$sId] = $stScores->sum('computed_total');
                }
            }

            $highest = count($subjectClassTotals) > 0 ? max($subjectClassTotals) : 0;
            $lowest = count($subjectClassTotals) > 0 ? min($subjectClassTotals) : 0;
            $subjRanks = $this->rankingService->calculatePositions($subjectClassTotals);

            $annualSubjectRows[] = [
                'subject_id'       => $subjectId,
                'subject_name'     => $subjectName,
                'term1_total'      => $term1 ? $t1Score : '-',
                'term2_total'      => $term2 ? $t2Score : '-',
                'term3_total'      => $term3 ? $t3Score : '-',
                'yearly_total'     => $yearlyTotal,
                'annual_average'   => $annualAvg,
                'grade'            => $scale['grade'],
                'remark'           => $scale['remark'],
                'highest_in_class' => $highest,
                'lowest_in_class'  => $lowest,
                'subject_position' => $subjRanks[$studentId] ?? '-',
            ];
        }

        $subjectCount = count($annualSubjectRows);
        $finalAnnualAverage = $subjectCount > 0 ? round($annualGrandTotal / ($subjectCount * 3), 2) : 0;

        $student = Student::with('classroom')->find($studentId);

        return [
            'student'                 => $student,
            'session'                 => '2025/2026',
            'annual_subjects'         => $annualSubjectRows,
            'annual_grand_total'      => $annualGrandTotal,
            'final_annual_average'    => $finalAnnualAverage,
            'annual_class_position'   => $annualClassPositions[$studentId] ?? '-',
            'total_students_in_class' => count($classStudentAnnualTotals),
            'remarks'                 => [
                'form_master_remark' => $finalAnnualAverage >= 70 ? 'An outstanding academic performance throughout the session.' : 'Satisfactory yearly progress.',
                'principal_remark'   => $finalAnnualAverage >= 50 ? 'Promoted to the next academic level.' : 'Repeat session advised.',
            ]
        ];
    }
}
