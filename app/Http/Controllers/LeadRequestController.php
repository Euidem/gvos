<?php

namespace App\Http\Controllers;

use App\Models\LeadRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadRequestController extends Controller
{
    /** Timezone options for the public form (matches GVOS standard list). */
    public const TIMEZONES = [
        'Africa/Lagos'        => 'Africa/Lagos (WAT)',
        'UTC'                 => 'UTC',
        'Europe/London'       => 'Europe/London (GMT/BST)',
        'Europe/Paris'        => 'Europe/Paris (CET/CEST)',
        'Europe/Berlin'       => 'Europe/Berlin (CET/CEST)',
        'America/New_York'    => 'America/New_York (EST/EDT)',
        'America/Chicago'     => 'America/Chicago (CST/CDT)',
        'America/Denver'      => 'America/Denver (MST/MDT)',
        'America/Los_Angeles' => 'America/Los_Angeles (PST/PDT)',
        'America/Toronto'     => 'America/Toronto (EST/EDT)',
        'America/Vancouver'   => 'America/Vancouver (PST/PDT)',
    ];

    /** Role types offered in the form. */
    public const ROLES = [
        'virtual_assistant'    => 'Virtual Assistant',
        'executive_assistant'  => 'Executive Assistant',
        'social_media_manager' => 'Social Media Manager',
        'video_editor'         => 'Video Editor',
        'developer'            => 'Developer',
        'designer'             => 'Designer',
        'motion_graphics'      => 'Motion Graphics',
        'other'                => 'Other (please specify)',
    ];

    /** Budget range options. */
    public const BUDGET_RANGES = [
        'under_500'    => 'Under $500 / month',
        '500_1000'     => '$500 – $1,000 / month',
        '1000_2000'    => '$1,000 – $2,000 / month',
        '2000_3500'    => '$2,000 – $3,500 / month',
        '3500_plus'    => '$3,500+ / month',
        'not_sure'     => 'Not sure yet',
    ];

    public function show(): View
    {
        return view('lead.request-service', [
            'timezones'    => self::TIMEZONES,
            'roles'        => self::ROLES,
            'budgetRanges' => self::BUDGET_RANGES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'               => ['required', 'string', 'max:100'],
            'last_name'                => ['required', 'string', 'max:100'],
            'email'                    => ['required', 'email', 'max:255'],
            'phone'                    => ['nullable', 'string', 'max:50'],
            'country'                  => ['nullable', 'string', 'max:100'],
            'city'                     => ['nullable', 'string', 'max:100'],
            'timezone'                 => ['nullable', 'string', 'max:100'],
            'client_type'              => ['required', 'in:individual,business'],
            'company_name'             => ['nullable', 'string', 'max:255'],
            'company_website'          => ['nullable', 'url', 'max:255'],
            'company_email_domain'     => ['nullable', 'string', 'max:255'],
            'role_needed'              => ['nullable', 'in:' . implode(',', array_keys(self::ROLES))],
            'role_needed_other'        => ['nullable', 'string', 'max:255'],
            'estimated_hours_per_week' => ['nullable', 'integer', 'min:1', 'max:168'],
            'preferred_start_date'     => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_work_schedule'  => ['nullable', 'string', 'max:255'],
            'required_skills'          => ['nullable', 'string', 'max:1000'],
            'work_description'         => ['nullable', 'string', 'max:5000'],
            'budget_range'             => ['nullable', 'in:' . implode(',', array_keys(self::BUDGET_RANGES))],
            'source'                   => ['nullable', 'string', 'max:255'],
        ]);

        $lead = LeadRequest::create($validated);

        // Log to audit trail (actor_id null for public actions)
        AuditLogger::log('lead_request.created', $lead, [
            'email'       => $lead->email,
            'client_type' => $lead->client_type,
            'role_needed' => $lead->role_needed,
        ], actorId: null);

        return redirect()->route('lead.request-service.success');
    }

    public function success(): View
    {
        return view('lead.request-service-success');
    }
}
