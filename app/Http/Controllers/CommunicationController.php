<?php

namespace App\Http\Controllers;

use App\Models\CommunicationCampaign;
use App\Models\CommunicationTemplate;
use App\Models\CommunicationSetting;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    /**
     * Get communication campaigns.
     */
    public function getCampaigns(Request $request)
    {
        $schoolId = $request->query('school_id', 1);

        $campaigns = CommunicationCampaign::with('template')
            ->where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $campaigns,
        ]);
    }

    /**
     * Create/Send communication campaign (SMS, Email, Broadcast).
     */
    public function createCampaign(Request $request)
    {
        $validated = $request->validate([
            'school_id'   => 'required|integer',
            'title'       => 'required|string|max:255',
            'type'        => 'required|string',
            'channel'     => 'required|string',
            'template_id' => 'nullable|string',
            'subject'     => 'nullable|string',
            'body_html'   => 'nullable|string',
            'status'      => 'nullable|string',
        ]);

        $campaign = CommunicationCampaign::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Communication campaign created & queued.',
            'data'    => $campaign,
        ], 210);
    }

    /**
     * Get communication templates.
     */
    public function getTemplates(Request $request)
    {
        $schoolId = $request->query('school_id', 1);

        $templates = CommunicationTemplate::where('school_id', $schoolId)
            ->orWhere('is_system', true)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $templates,
        ]);
    }
}
