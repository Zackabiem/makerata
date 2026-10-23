<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentActivationController extends Controller
{
    /**
     * Public Student Lookup for Activation
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'admission_no' => 'required|string',
            'parent_phone_last_4' => 'required|string|size:4',
        ]);

        $student = Student::with('school')
            ->where('admission_no', $request->admission_no)
            ->first();

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student record not found with the provided admission number.'
            ], 404);
        }

        // License Gate: Check if school plan is Super
        if (!$student->school || $student->school->plan !== 'Super') {
            return response()->json([
                'status' => 'error',
                'message' => 'Student portal activation is available exclusively for Super plan subscribers. Please contact your school administrator to upgrade.'
            ], 403);
        }

        if ($student->user_id && User::where('user_id', $student->user_id)->where('is_active', true)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This student account has already been activated. Please proceed to login.'
            ], 422);
        }

        // Verify parent phone challenge
        $cleanPhone = preg_replace('/\D/', '', $student->parent_phone ?? '');
        $parentLast4 = substr($cleanPhone, -4);

        if ($parentLast4 !== $request->parent_phone_last_4) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parent phone verification failed. The last 4 digits do not match our records.'
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Student profile verified',
            'data' => [
                'student_id' => $student->student_id,
                'admission_no' => $student->admission_no,
                'surname' => $student->surname,
                'first_name' => $student->first_name,
                'full_name' => $student->full_name,
                'parent_name' => $student->parent_name,
                'school_name' => $student->school ? $student->school->school_name : null,
                'plan' => $student->school ? $student->school->plan : null,
            ]
        ]);
    }

    /**
     * Activate Student Account and Create Credentials
     */
    public function activate(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'parent_phone_last_4' => 'required|string|size:4',
            'email' => 'nullable|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $student = Student::with('school')->find($request->student_id);

        if (!$student->school || $student->school->plan !== 'Super') {
            return response()->json([
                'status' => 'error',
                'message' => 'Student portal activation is available exclusively for Super plan subscribers. Please contact your school administrator to upgrade.'
            ], 403);
        }

        if ($student->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This student account is already active.'
            ], 422);
        }

        // Verify parent phone challenge
        $cleanPhone = preg_replace('/\D/', '', $student->parent_phone ?? '');
        $parentLast4 = substr($cleanPhone, -4);

        if ($parentLast4 !== $request->parent_phone_last_4) {
            return response()->json([
                'status' => 'error',
                'message' => 'Security challenge failed: parent phone last 4 digits mismatch.'
            ], 422);
        }

        $emailToUse = $request->email ?? $student->parent_email ?? strtolower($student->admission_no) . '@student.school';

        $user = DB::transaction(function () use ($student, $emailToUse, $request) {
            $user = User::create([
                'email' => $emailToUse,
                'password' => Hash::make($request->password),
                'name' => $student->full_name,
                'role' => 'student',
                'school_id' => $student->school_id,
                'phone' => $student->parent_phone,
                'is_active' => true,
            ]);

            $student->user_id = $user->user_id;
            $student->save();

            return $user;
        });

        $user->tokens()->delete();
        $token = $user->createToken('edupro-token')->plainTextToken;
        $user->load(['school', 'student']);

        return response()->json([
            'status' => 'success',
            'message' => 'Student account successfully activated!',
            'data' => [
                'token' => $token,
                'user' => $user,
            ]
        ]);
    }
}
