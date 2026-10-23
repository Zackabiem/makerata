<?php

namespace App\Http\Controllers;

use App\Models\SchoolProfile;
use App\Models\SubscriptionPlan;
use App\Models\SchoolSubscription;
use App\Models\Payment;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;
use Illuminate\Http\Request;

class SystemAdminController extends Controller
{
    /**
     * Get Global Multi-Tenant System Admin Summary Metrics.
     */
    public function summary(Request $request)
    {
        $totalSchools = SchoolProfile::count();
        $totalStudents = Student::count();
        $totalStaff = Staff::count();
        $totalUsers = User::count();
        $totalRevenue = Payment::sum('amount_paid');
        $activeSubscriptions = SchoolSubscription::where('status', 'active')->count();

        $recentSchools = SchoolProfile::orderBy('created_at', 'desc')->limit(5)->get();

        return response()->json([
            'status' => 'success',
            'role'   => 'system_admin',
            'data'   => [
                'total_schools'        => $totalSchools,
                'total_students'       => $totalStudents,
                'total_staff'          => $totalStaff,
                'total_users'          => $totalUsers,
                'total_platform_mrr'   => $totalRevenue,
                'active_subscriptions' => $activeSubscriptions,
                'recent_schools'       => $recentSchools,
            ]
        ]);
    }

    /**
     * List all tenant schools across the network.
     */
    public function listSchools(Request $request)
    {
        $query = SchoolProfile::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('school_name', 'LIKE', "%{$search}%")
                  ->orWhere('school_code', 'LIKE', "%{$search}%")
                  ->orWhere('email_address', 'LIKE', "%{$search}%");
        }

        $schools = $query->orderBy('id', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $schools,
        ]);
    }

    /**
     * Provision a new school profile (System Admin onboarding).
     */
    public function storeSchool(Request $request)
    {
        $validated = $request->validate([
            'school_name'    => 'required|string|max:255',
            'email_address'  => 'required|email|max:255',
            'phone_number'   => 'nullable|string|max:50',
            'school_address' => 'nullable|string|max:500',
            'headteacher_name' => 'nullable|string|max:255',
            'motto'          => 'nullable|string|max:255',
        ]);

        $schoolCode = 'EDUPRO-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $school = SchoolProfile::create([
            'school_name' => $validated['school_name'],
            'school_code' => $schoolCode,
            'email_address' => $validated['email_address'],
            'phone_number' => $validated['phone_number'] ?? null,
            'school_address' => $validated['school_address'] ?? null,
            'headteacher_name' => $validated['headteacher_name'] ?? 'School Administrator',
            'motto' => $validated['motto'] ?? 'Excellence and Integrity',
            'promotion_cutoff_average' => 50.00,
            'report_template_config' => [
                'theme_color' => '#1e3a8a',
                'active_session' => '2025/2026',
                'active_term' => 'Term 1',
            ],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'New school tenant provisioned successfully.',
            'data'    => $school,
        ], 201);
    }

    /**
     * List all platform billing transactions across all schools.
     */
    public function billingActivity(Request $request)
    {
        $payments = Payment::with(['student', 'school'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data'   => $payments,
        ]);
    }

    /**
     * Manage SaaS Subscription Plans (Create/Update tiers).
     */
    public function savePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id'             => 'nullable|integer',
            'plan_name'           => 'required|string|max:100',
            'price_per_term'      => 'required|numeric|min:0',
            'price_annual'        => 'required|numeric|min:0',
            'max_students'        => 'required|integer|min:1',
            'features'            => 'nullable|array',
            'is_custom_enterprise'=> 'boolean',
        ]);

        if (!empty($validated['plan_id'])) {
            $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
            $plan->update($validated);
        } else {
            $plan = SubscriptionPlan::create($validated);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'SaaS subscription tier updated successfully.',
            'data'    => $plan,
        ]);
    }
}
