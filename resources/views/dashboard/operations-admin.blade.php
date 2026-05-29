<x-layouts.gvos title="Operations Dashboard">
@php
    $user = auth()->user();
    $profile = $user->profile;
    $companyCount  = \App\Models\Company::count();
    $talentCount   = \App\Models\TalentProfile::count();
    $managerCount  = \App\Models\ManagerProfile::count();
    $clientCount   = \App\Models\ClientProfile::count();

    // Lead pipeline counts
    $leadTotal          = \App\Models\LeadRequest::count();
    $leadNew            = \App\Models\LeadRequest::where('status', 'new')->count();
    $leadUnderReview    = \App\Models\LeadRequest::where('status', 'under_review')->count();
    $leadTrialApproved  = \App\Models\LeadRequest::where('status', 'trial_approved')->count();
    $leadTrialActive    = \App\Models\LeadRequest::where('status', 'trial_active')->count();
    $leadPaymentPending = \App\Models\LeadRequest::where('status', 'payment_pending')->count();
@endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">GVOS Ops Console — Operations Administrator</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1 rounded-full font-medium">
                Operations Admin
            </span>
        </div>
    </div>

    {{-- ── Entity counts ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="/admin/companies"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all">
            <p class="text-2xl font-bold text-slate-800">{{ $companyCount }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Companies</p>
        </a>
        <a href="/admin/talent-profiles"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all">
            <p class="text-2xl font-bold text-slate-800">{{ $talentCount }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Talent Profiles</p>
        </a>
        <a href="/admin/manager-profiles"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all">
            <p class="text-2xl font-bold text-slate-800">{{ $managerCount }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Manager Profiles</p>
        </a>
        <a href="/admin/client-profiles"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all">
            <p class="text-2xl font-bold text-slate-800">{{ $clientCount }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Client Profiles</p>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <a href="/admin/users"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">View Users</p>
                    <p class="text-xs text-slate-400">Browse all platform users</p>
                </div>
            </div>
        </a>

        <a href="{{ route('profile.show') }}"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-slate-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">My Profile</p>
                    <p class="text-xs text-slate-400">Update your details and password</p>
                </div>
            </div>
        </a>
    </div>

    {{-- ── Lead pipeline ───────────────────────────────────────────────── --}}
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-slate-700 mb-3 uppercase tracking-wide">Lead Pipeline</h3>
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
            <a href="/admin/lead-requests"
               class="bg-white rounded-xl border border-slate-200 px-4 py-3 hover:border-indigo-300 hover:shadow-sm transition-all">
                <p class="text-2xl font-bold text-slate-800">{{ $leadTotal }}</p>
                <p class="text-xs text-slate-500 mt-1 font-medium">Total Leads</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=new"
               class="bg-white rounded-xl border border-slate-200 px-4 py-3 hover:border-amber-300 hover:shadow-sm transition-all">
                <p class="text-2xl font-bold text-amber-600">{{ $leadNew }}</p>
                <p class="text-xs text-slate-500 mt-1 font-medium">New</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=under_review"
               class="bg-white rounded-xl border border-slate-200 px-4 py-3 hover:border-indigo-300 hover:shadow-sm transition-all">
                <p class="text-2xl font-bold text-slate-800">{{ $leadUnderReview }}</p>
                <p class="text-xs text-slate-500 mt-1 font-medium">Under Review</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=trial_approved"
               class="bg-white rounded-xl border border-slate-200 px-4 py-3 hover:border-emerald-300 hover:shadow-sm transition-all">
                <p class="text-2xl font-bold text-emerald-600">{{ $leadTrialApproved }}</p>
                <p class="text-xs text-slate-500 mt-1 font-medium">Trial Approved</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=trial_active"
               class="bg-white rounded-xl border border-slate-200 px-4 py-3 hover:border-emerald-300 hover:shadow-sm transition-all">
                <p class="text-2xl font-bold text-emerald-700">{{ $leadTrialActive }}</p>
                <p class="text-xs text-slate-500 mt-1 font-medium">Trial Active</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=payment_pending"
               class="bg-white rounded-xl border border-slate-200 px-4 py-3 hover:border-amber-300 hover:shadow-sm transition-all">
                <p class="text-2xl font-bold text-amber-700">{{ $leadPaymentPending }}</p>
                <p class="text-xs text-slate-500 mt-1 font-medium">Payment Pending</p>
            </a>
        </div>
    </div>

    <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-6 py-5">
        <p class="text-sm font-semibold text-indigo-800">Phase 3 — Leads and Trial Flow</p>
        <p class="text-sm text-indigo-700 mt-0.5">
            Lead requests, price estimates and trial management are live. Workspace management and advanced client features are coming in later phases.
        </p>
    </div>

</x-layouts.gvos>
