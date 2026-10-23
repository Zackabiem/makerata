<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Payment;
use App\Models\Homework;

class DashboardController extends Controller
{
    /**
     * Get System Admin / Headteacher Overview Metrics.
     */
    public function adminSummary(Request $request)
    {
        $schoolId = $request->query('school_id', 1);

        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalStaff = Staff::where('school_id', $schoolId)->count();
        $totalClasses = Classroom::where('school_id', $schoolId)->count();
        $totalSubjects = Subject::where('school_id', $schoolId)->count();
        $totalRevenue = Payment::where('school_id', $schoolId)->sum('amount_paid');

        return response()->json([
            'status' => 'success',
            'role'   => 'admin',
            'data'   => [
                'total_students' => $totalStudents,
                'total_staff'    => $totalStaff,
                'total_classes'  => $totalClasses,
                'total_subjects' => $totalSubjects,
                'total_revenue'  => $totalRevenue,
            ]
        ]);
    }

    /**
     * Get Teacher Overview Metrics.
     */
    public function teacherSummary(Request $request)
    {
        $schoolId = $request->query('school_id', 1);
        $staffId = $request->query('staff_id');

        $assignedClasses = Classroom::where('school_id', $schoolId)
            ->where('form_master_id', $staffId)
            ->count();

        $activeHomeworks = Homework::where('school_id', $schoolId)
            ->when($staffId, function ($q) use ($staffId) {
                return $q->where('staff_id', $staffId);
            })
            ->count();

        return response()->json([
            'status' => 'success',
            'role'   => 'teacher',
            'data'   => [
                'assigned_classes' => $assignedClasses,
                'active_homeworks' => $activeHomeworks,
            ]
        ]);
    }

    /**
     * Get Cashier / Bursary Overview Metrics.
     */
    public function cashierSummary(Request $request)
    {
        $schoolId = $request->query('school_id', 1);

        $totalCollected = Payment::where('school_id', $schoolId)->sum('amount_paid');
        $recentPayments = Payment::with('student')
            ->where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'role'   => 'cashier',
            'data'   => [
                'total_collected' => $totalCollected,
                'recent_payments' => $recentPayments,
            ]
        ]);
    }

    /**
     * Get Student Portal Overview Metrics.
     */
    public function studentSummary(Request $request)
    {
        $studentId = $request->query('student_id');

        $student = Student::find($studentId);

        return response()->json([
            'status' => 'success',
            'role'   => 'student',
            'data'   => [
                'student' => $student,
            ]
        ]);
    }
}
