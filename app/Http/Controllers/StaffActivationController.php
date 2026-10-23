<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffActivationController extends Controller
{
    /**
     * Public Staff Lookup for Activation
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone_last_4' => 'required|string|size:4',
        ]);

        $staff = Staff::with('school')
            ->where('email', $request->email)
            ->first();

        if (!$staff) {
            return response()->json([
                'status' => 'error',
                'message' => 'Staff record not found with the provided email address.'
            ], 404);
        }

        if ($staff::where('staff_id', $staff->staff_id)->where('status', 'active')->whereNotNull('user_id')->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This staff account has already been activated. Please proceed to login.'
            ], 422);
        }

        // Validate phone last 4 digits challenge
        $cleanPhone = preg_replace('/\D/', '', $staff->phone ?? '');
        $staffLast4 = substr($cleanPhone, -4);

        if ($staffLast4 !== $request->phone_last_4) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number verification failed. The last 4 digits do not match our records.'
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Staff profile verified',
            'data' => [
                'staff_id' => $staff->staff_id,
                'surname' => $staff->surname,
                'first_name' => $staff->first_name,
                'full_name' => $staff->full_name,
                'email' => $staff->email,
                'role' => $staff->role,
                'school_name' => $staff->school ? $staff->school->school_name : null,
            ]
        ]);
    }

    /**
     * Activate Staff Account and Create Credentials
     */
    public function activate(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,staff_id',
            'phone_last_4' => 'required|string|size:4',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $staff = Staff::with('school')->find($request->staff_id);

        if ($staff->status === 'active' && $staff->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This staff account is already active.'
            ], 422);
        }

        // Phone challenge verification
        $cleanPhone = preg_replace('/\D/', '', $staff->phone ?? '');
        $staffLast4 = substr($cleanPhone, -4);

        if ($staffLast4 !== $request->phone_last_4) {
            return response()->json([
                'status' => 'error',
                'message' => 'Security challenge failed: phone last 4 digits mismatch.'
            ], 422);
        }

        $user = DB::transaction(function () use ($staff, $request) {
            $existingUser = User::where('email', $staff->email)->first();

            if ($existingUser) {
                $existingUser->update([
                    'password' => Hash::make($request->password),
                    'name' => $staff->full_name,
                    'role' => $staff->role ?? 'teacher',
                    'school_id' => $staff->school_id,
                    'phone' => $staff->phone,
                    'is_active' => true,
                ]);
                $user = $existingUser;
            } else {
                $user = User::create([
                    'email' => $staff->email,
                    'password' => Hash::make($request->password),
                    'name' => $staff->full_name,
                    'role' => $staff->role ?? 'teacher',
                    'school_id' => $staff->school_id,
                    'phone' => $staff->phone,
                    'is_active' => true,
                ]);
            }

            $staff->user_id = $user->user_id;
            $staff->status = 'active';
            $staff->save();

            return $user;
        });

        $user->tokens()->delete();
        $token = $user->createToken('edupro-token')->plainTextToken;
        $user->load(['school', 'staff']);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff account successfully activated!',
            'data' => [
                'token' => $token,
                'user' => $user,
            ]
        ]);
    }
}
