<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Get attendance records for a class/term.
     */
    public function index(Request $request)
    {
        $schoolId  = $request->query('school_id', 1);
        $classId   = $request->query('class_id');
        $sessionId = $request->query('session_id', 1);
        $termId    = $request->query('term_id', 1);
        $studentId = $request->query('student_id');

        $query = AttendanceRecord::with('student')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($studentId) {
            $query->where('student_id', $studentId);
            $record = $query->first();

            if (!$record) {
                // Default attendance values
                return response()->json([
                    'status' => 'success',
                    'data'   => [
                        'school_id'          => $schoolId,
                        'student_id'         => (int) $studentId,
                        'class_id'           => (int) $classId,
                        'session_id'         => (int) $sessionId,
                        'term_id'            => (int) $termId,
                        'days_school_opened' => 115,
                        'days_present'       => 110,
                        'days_absent'        => 5,
                        'remarks'            => 'Punctual and attentive.',
                    ],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $record,
            ]);
        }

        $records = $query->get()->keyBy('student_id');

        return response()->json([
            'status' => 'success',
            'data'   => $records,
        ]);
    }

    /**
     * Batch save student attendance for class.
     */
    public function batchSave(Request $request)
    {
        $validated = $request->validate([
            'school_id'    => 'required|integer',
            'class_id'     => 'required|integer',
            'session_id'   => 'required|integer',
            'term_id'      => 'required|integer',
            'attendance'   => 'required|array',
            'attendance.*.student_id'          => 'required|integer',
            'attendance.*.days_school_opened' => 'required|integer|min:0',
            'attendance.*.days_present'       => 'required|integer|min:0',
            'attendance.*.days_absent'        => 'nullable|integer|min:0',
            'attendance.*.remarks'            => 'nullable|string',
        ]);

        $schoolId  = $validated['school_id'];
        $classId   = $validated['class_id'];
        $sessionId = $validated['session_id'];
        $termId    = $validated['term_id'];

        DB::beginTransaction();
        try {
            foreach ($validated['attendance'] as $item) {
                $opened  = (int) $item['days_school_opened'];
                $present = (int) $item['days_present'];
                $absent  = isset($item['days_absent']) ? (int) $item['days_absent'] : max(0, $opened - $present);

                AttendanceRecord::updateOrCreate(
                    [
                        'school_id'  => $schoolId,
                        'student_id' => $item['student_id'],
                        'class_id'   => $classId,
                        'session_id' => $sessionId,
                        'term_id'    => $termId,
                    ],
                    [
                        'days_school_opened' => $opened,
                        'days_present'       => $present,
                        'days_absent'        => $absent,
                        'remarks'            => $item['remarks'] ?? 'Regular attendance',
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Attendance register updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save attendance records: ' . $e->getMessage(),
            ], 500);
        }
    }
}
