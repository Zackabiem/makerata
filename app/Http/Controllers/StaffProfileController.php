<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffProfileController extends Controller
{
    /**
     * Get Teacher/Staff Profile Information
     */
    public function show(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            $staffId = $request->query('staff_id');
            $staff = Staff::with('user')->find($staffId);
        } else {
            $user->load(['staff', 'school']);
            $staff = $user->staff;
        }

        if (!$staff) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Staff profile record not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'staff_id'   => $staff->staff_id,
                'user_id'    => $staff->user_id,
                'first_name' => $staff->first_name,
                'surname'    => $staff->surname,
                'email'      => $staff->email,
                'phone'      => $staff->phone,
                'address'    => $staff->address,
                'role'       => $staff->role,
                'status'     => $staff->status,
                'school'     => $user ? $user->school : null,
            ],
        ]);
    }

    /**
     * Update Teacher/Staff Profile (House Address, Phone Number, Password)
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'staff_id'         => 'nullable|integer',
            'phone'            => 'required|string|max:20',
            'address'          => 'required|string|max:500',
            'current_password' => 'nullable|string',
            'new_password'     => 'nullable|string|min:6',
        ]);

        $staff = null;
        if ($user) {
            $staff = Staff::where('user_id', $user->user_id)->orWhere('email', $user->email)->first();
        } elseif (!empty($validated['staff_id'])) {
            $staff = Staff::find($validated['staff_id']);
            if ($staff && $staff->user_id) {
                $user = User::find($staff->user_id);
            }
        }

        if (!$staff) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unable to resolve staff record for update.',
            ], 404);
        }

        // 1. Update Staff Phone & Address
        $staff->phone   = $validated['phone'];
        $staff->address = $validated['address'];
        $staff->save();

        // 2. Update linked User Phone
        if ($user) {
            $user->phone = $validated['phone'];

            // 3. Update Password if provided
            if (!empty($validated['new_password'])) {
                if (!empty($validated['current_password'])) {
                    if (!Hash::check($validated['current_password'], $user->password)) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'The current password provided is incorrect.',
                        ], 422);
                    }
                }

                $user->password = Hash::make($validated['new_password']);
            }

            $user->save();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully.',
            'data'    => [
                'staff_id' => $staff->staff_id,
                'phone'    => $staff->phone,
                'address'  => $staff->address,
                'email'    => $staff->email,
            ],
        ]);
    }
}
