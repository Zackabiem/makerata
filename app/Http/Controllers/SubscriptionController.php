<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\SchoolSubscription;
use App\Models\SchoolProfile;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Get available SaaS plans catalog.
     */
    public function getPlans()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();

        if ($plans->isEmpty()) {
            $defaults = [
                [
                    'name'              => 'Basic Starter',
                    'slug'              => 'basic',
                    'description'       => 'Essential tools for small nursery and primary schools',
                    'base_price'        => 25000.00,
                    'price_per_student' => 500.00,
                    'billing_period'    => 'term',
                    'max_students'      => 150,
                    'is_active'         => true,
                ],
                [
                    'name'              => 'Standard Growth',
                    'slug'              => 'standard',
                    'description'       => 'Full academic scoring, report cards & finance module',
                    'base_price'        => 50000.00,
                    'price_per_student' => 750.00,
                    'billing_period'    => 'term',
                    'max_students'      => 500,
                    'is_active'         => true,
                ],
                [
                    'name'              => 'Enterprise Elite',
                    'slug'              => 'enterprise',
                    'description'       => 'Unlimited student roster, custom branding, priority support',
                    'base_price'        => 100000.00,
                    'price_per_student' => 1000.00,
                    'billing_period'    => 'term',
                    'max_students'      => 2000,
                    'is_active'         => true,
                ],
            ];

            foreach ($defaults as $def) {
                SubscriptionPlan::create($def);
            }

            $plans = SubscriptionPlan::where('is_active', true)->get();
        }

        return response()->json([
            'status' => 'success',
            'data'   => $plans,
        ]);
    }

    /**
     * Get current school subscription status.
     */
    public function getSchoolSubscription(Request $request)
    {
        $schoolId = $request->query('school_id', 1);

        $subscription = SchoolSubscription::with(['plan', 'school'])
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => $subscription,
        ]);
    }
}
