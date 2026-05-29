<x-layouts.gvos title="Ops Console">
@php
    $user = auth()->user();
    $profile = $user->profile;
    $companyCount      = \App\Models\Company::count();
    $talentCount       = \App\Models\TalentProfile::count();
    $managerCount      = \App\Models\ManagerProfile::count();
    $clientCount       = \App\Models\ClientProfile::count();
    $workspaceCount    = \App\Models\Workspace::count();
    $workspaceActive   = \App\Models\Workspace::where('status', 'active')->count();

    // Lead pipeline counts
    $leadTotal         = \App\Models\LeadRequest::count();
    $leadNew           = \App\Models\LeadRequest::where('status', 'new')->count();
    $leadUnderReview   = \App\Models\LeadRequest::where('status', 'under_review')->count();
    $leadTrialApproved = \App\Models\LeadRequest::where('status', 'trial_approved')->count();
    $leadTrialActive   = \App\Models\LeadRequest::where('status', 'trial_active')->count();
    $leadPaymentPending = \App\Models\LeadRequest::where('status', 'payment_pending')->count();
@endphp

    {{-- ── Welcome header ──────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-on-surface">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-on-surface-variant mt-1">GVOS Ops Console — Super Administrator</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-status-active/10 text-status-active border border-status-active/20 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-secondary/5 text-secondary border border-secondary/20 px-3 py-1 rounded-full font-medium">
                Super Admin
            </span>
        </div>
    </div>

    {{-- ── Entity counts ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <a href="/admin/companies"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-outline" style="font-size: 16px;">business</span>
            </div>
            <p class="text-2xl font-bold text-on-surface">{{ $companyCount }}</p>
            <p class="text-xs text-on-surface-variant mt-1 font-medium">Companies</p>
        </a>
        <a href="/admin/talent-profiles"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-outline" style="font-size: 16px;">badge</span>
            </div>
            <p class="text-2xl font-bold text-on-surface">{{ $talentCount }}</p>
            <p class="text-xs text-on-surface-variant mt-1 font-medium">Talent Profiles</p>
        </a>
        <a href="/admin/manager-profiles"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-outline" style="font-size: 16px;">manage_accounts</span>
            </div>
            <p class="text-2xl font-bold text-on-surface">{{ $managerCount }}</p>
            <p class="text-xs text-on-surface-variant mt-1 font-medium">Manager Profiles</p>
        </a>
        <a href="/admin/client-profiles"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-outline" style="font-size: 16px;">group</span>
            </div>
            <p class="text-2xl font-bold text-on-surface">{{ $clientCount }}</p>
            <p class="text-xs text-on-surface-variant mt-1 font-medium">Client Profiles</p>
        </a>
        <a href="/admin/workspaces"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm sm:col-span-2 lg:col-span-1">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-outline" style="font-size: 16px;">workspaces</span>
            </div>
            <p class="text-2xl font-bold text-on-surface">{{ $workspaceActive }}<span class="text-base font-normal text-outline">/{{ $workspaceCount }}</span></p>
            <p class="text-xs text-on-surface-variant mt-1 font-medium">Active Workspaces</p>
        </a>
    </div>

    {{-- ── Quick-action links ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <a href="/admin/users"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm group">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center group-hover:bg-secondary/10 transition-colors">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">group</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-on-surface">Manage Users</p>
                    <p class="text-xs text-outline">View, create and manage all users</p>
                </div>
            </div>
        </a>

        <a href="/admin/companies"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm group">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center group-hover:bg-secondary/10 transition-colors">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">business</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-on-surface">Companies</p>
                    <p class="text-xs text-outline">Manage client companies</p>
                </div>
            </div>
        </a>

        <a href="{{ route('profile.show') }}"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm group">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-surface-container-low rounded-lg flex items-center justify-center group-hover:bg-secondary/5 transition-colors">
                    <span class="material-symbols-outlined text-on-surface-variant" style="font-size: 18px;">person</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-on-surface">My Profile</p>
                    <p class="text-xs text-outline">Update your details and password</p>
                </div>
            </div>
        </a>
    </div>

    {{-- ── Lead pipeline ───────────────────────────────────────────────── --}}
    <div class="mb-8">
        <h3 class="text-xs font-semibold text-outline mb-3 uppercase tracking-wider">Lead Pipeline</h3>
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
            <a href="/admin/lead-requests"
               class="bg-white rounded-xl border border-border-subtle px-4 py-3 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-on-surface">{{ $leadTotal }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Total Leads</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=new"
               class="bg-white rounded-xl border border-border-subtle px-4 py-3 hover:border-status-payment-due/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-status-payment-due">{{ $leadNew }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">New</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=under_review"
               class="bg-white rounded-xl border border-border-subtle px-4 py-3 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-on-surface">{{ $leadUnderReview }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Under Review</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=trial_approved"
               class="bg-white rounded-xl border border-border-subtle px-4 py-3 hover:border-status-active/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-status-active">{{ $leadTrialApproved }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Trial Approved</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=trial_active"
               class="bg-white rounded-xl border border-border-subtle px-4 py-3 hover:border-status-completed/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-status-completed">{{ $leadTrialActive }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Trial Active</p>
            </a>
            <a href="/admin/lead-requests?tableFilters[status][value]=payment_pending"
               class="bg-white rounded-xl border border-border-subtle px-4 py-3 hover:border-status-payment-due/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-status-payment-due">{{ $leadPaymentPending }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Payment Pending</p>
            </a>
        </div>
    </div>

    {{-- ── Phase 4 status notice ────────────────────────────────────────── --}}
    <div class="bg-secondary/5 border border-secondary/20 rounded-xl px-6 py-5 flex items-start gap-3">
        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size: 18px;">info</span>
        <div>
            <p class="text-sm font-semibold text-secondary">Phase 4 — Workspace Engine</p>
            <p class="text-sm text-on-surface-variant mt-0.5">
                Workspaces can now be created from trials. Members are tracked with roles.
                Task boards, file sharing, chat and billing are coming in later phases.
            </p>
        </div>
    </div>

</x-layouts.gvos>
