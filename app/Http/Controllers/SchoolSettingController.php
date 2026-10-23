<?php

namespace App\Http\Controllers;

use App\Models\SchoolProfile;
use Illuminate\Http\Request;

class SchoolSettingController extends Controller
{
    /**
     * Get current school profile settings, template config, and branding assets.
     */
    public function show(Request $request)
    {
        $schoolId = $request->input('school_id') ?? 1;
        $school = SchoolProfile::find($schoolId);

        if (!$school) {
            $school = SchoolProfile::create([
                'id' => $schoolId,
                'school_name' => 'EduPro Ledger Academy',
                'school_code' => 'EDUPRO-' . rand(1000, 9999),
                'motto' => 'Excellence and Integrity in Education',
                'school_address' => '12 Education Way, Knowledge City',
                'phone_number' => '+234 800 123 4567',
                'email_address' => 'info@edupro.school',
                'headteacher_name' => 'Dr. Clement Okafor',
                'promotion_cutoff_average' => 50.00,
                'report_template_config' => [
                    'theme_color' => '#1e3a8a',
                    'font_family' => 'Helvetica',
                    'show_position' => true,
                    'show_highest_lowest' => true,
                    'show_grade_key' => true,
                    'show_watermark' => true,
                    'active_session' => '2025/2026',
                    'active_term' => 'Term 3',
                    'next_term_begins' => '2026-09-15',
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $school,
        ]);
    }

    /**
     * Update school profile settings, report template configuration, and branding.
     */
    public function update(Request $request)
    {
        $schoolId = $request->input('school_id') ?? 1;
        $school = SchoolProfile::find($schoolId);

        if (!$school) {
            return response()->json([
                'status' => 'error',
                'message' => 'School profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'school_name' => 'nullable|string|max:255',
            'motto' => 'nullable|string|max:255',
            'school_address' => 'nullable|string|max:500',
            'phone_number' => 'nullable|string|max:50',
            'email_address' => 'nullable|email|max:255',
            'headteacher_name' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string',
            'school_stamp_svg' => 'nullable|string',
            'report_watermark' => 'nullable|string',
            'promotion_cutoff_average' => 'nullable|numeric|min:0|max:100',
            'report_template_config' => 'nullable|array',
        ]);

        $school->fill(array_filter($validated, fn($val) => $val !== null));

        if ($request->has('report_template_config')) {
            $currentConfig = $school->report_template_config ?? [];
            $school->report_template_config = array_merge($currentConfig, $request->input('report_template_config'));
        }

        $school->save();

        return response()->json([
            'status' => 'success',
            'message' => 'School profile and report template settings updated successfully.',
            'data' => $school,
        ]);
    }
}
