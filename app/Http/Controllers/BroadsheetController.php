<?php

namespace App\Http\Controllers;

use App\Models\SchoolProfile;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Student;
use App\Models\TerminalScoreSheet;
use App\Models\TerminalRemark;
use App\Models\Staff;
use Illuminate\Http\Request;

class BroadsheetController extends Controller
{
    /**
     * Generate Class Summary Broadsheet (JSON API or Printable Blade View).
     */
    public function getBroadsheet(Request $request)
    {
        $request->validate([
            'school_id'  => 'nullable|integer',
            'class_id'   => 'required|integer',
            'session_id' => 'nullable|integer',
            'term_id'    => 'nullable|integer',
        ]);

        $schoolId  = $request->input('school_id', 1);
        $classId   = $request->input('class_id');
        $sessionId = $request->input('session_id', 1);
        $termId    = $request->input('term_id', 1);

        $school    = SchoolProfile::find($schoolId) ?? SchoolProfile::first();
        $classroom = Classroom::find($classId);

        if (!$classroom) {
            return response()->json(['status' => 'error', 'message' => 'Classroom not found.'], 404);
        }

        $formMasterName = 'N/A';
        if ($classroom->form_master_id) {
            $staff = Staff::find($classroom->form_master_id);
            if ($staff) {
                $formMasterName = $staff->first_name . ' ' . $staff->surname;
            }
        }

        // Get subjects offered in this class or default active subjects
        $subjects = Subject::where('school_id', $schoolId)->get();

        // Get active students in class
        $students = Student::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('full_name', 'asc')
            ->get();

        // Fetch score sheet records
        $scoreSheets = TerminalScoreSheet::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->get();

        // Fetch terminal remarks
        $remarks = TerminalRemark::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->get()
            ->keyBy('student_id');

        // Group score sheets by student_id
        $scoresByStudent = $scoreSheets->groupBy('student_id');

        // Build student matrix
        $studentMatrix = [];
        foreach ($students as $student) {
            $studentScores = $scoresByStudent->get($student->id, collect());

            $subjectMap = [];
            $grandTotal = 0;
            $subjectCount = 0;

            foreach ($studentScores as $score) {
                $subjectMap[$score->subject_id] = [
                    'total_score' => $score->total_score,
                    'grade'       => $score->grade,
                    'remark'      => $score->remark,
                ];
                $grandTotal += $score->total_score;
                $subjectCount++;
            }

            $average = $subjectCount > 0 ? ($grandTotal / $subjectCount) : 0;
            $termRemark = $remarks->get($student->id);

            $studentMatrix[] = [
                'student_id'          => $student->id,
                'admission_no'        => $student->admission_no,
                'full_name'           => $student->full_name,
                'scores'              => $subjectMap,
                'grand_total'         => $grandTotal,
                'average'             => round($average, 2),
                'subject_count'       => $subjectCount,
                'form_master_remarks' => $termRemark->form_master_remark ?? '-',
                'headteacher_remarks' => $termRemark->headteacher_remark ?? '-',
                'class_position'      => null,
            ];
        }

        // Compute class ranking
        usort($studentMatrix, function ($a, $b) {
            return $b['grand_total'] <=> $a['grand_total'];
        });

        $rank = 1;
        foreach ($studentMatrix as $idx => &$item) {
            if ($idx > 0 && $item['grand_total'] < $studentMatrix[$idx - 1]['grand_total']) {
                $rank = $idx + 1;
            }
            $item['class_position'] = $rank;
        }
        unset($item);

        // Sort back alphabetically by full_name for clean class list
        usort($studentMatrix, function ($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });

        $data = [
            'school'      => $school,
            'classroom'   => $classroom,
            'session'     => '2025/2026',
            'term'        => $termId == 1 ? 'First Term' : ($termId == 2 ? 'Second Term' : 'Third Term'),
            'form_master' => $formMasterName,
            'subjects'    => $subjects,
            'students'    => $studentMatrix,
        ];

        if ($request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        }

        return view('reports.class_summary_broadsheet', $data);
    }

    /**
     * Print Subject Score Sheet (Blade View).
     */
    public function printSubjectScoresheet(Request $request)
    {
        $request->validate([
            'school_id'  => 'nullable|integer',
            'class_id'   => 'required|integer',
            'subject_id' => 'required|integer',
            'session_id' => 'nullable|integer',
            'term_id'    => 'nullable|integer',
        ]);

        $schoolId  = $request->input('school_id', 1);
        $classId   = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $sessionId = $request->input('session_id', 1);
        $termId    = $request->input('term_id', 1);

        $school    = SchoolProfile::find($schoolId) ?? SchoolProfile::first();
        $classroom = Classroom::find($classId);
        $subject   = Subject::find($subjectId);

        $teacherName = 'Subject Teacher';
        if ($classroom && $classroom->form_master_id) {
            $staff = Staff::find($classroom->form_master_id);
            if ($staff) {
                $teacherName = $staff->first_name . ' ' . $staff->surname;
            }
        }

        // Get students and scores
        $students = Student::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('full_name', 'asc')
            ->get();

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

        $data = [
            'school'       => $school,
            'classroom'    => $classroom,
            'subject'      => $subject,
            'session'      => '2025/2026',
            'term'         => $termId == 1 ? 'First Term' : ($termId == 2 ? 'Second Term' : 'Third Term'),
            'teacher_name' => $teacherName,
            'scoresList'   => $scoresList,
        ];

        if ($request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        }

        return view('reports.subject_scoresheet', $data);
    }
}
