<x-layouts.gvos :title="$workspace->name . ' Members'">

    @php
        $roleLabels = \App\Models\WorkspaceMember::roleLabels();
        $activeMembers = $workspace->activeMembers;
        $managerCount = $activeMembers->whereIn('role', ['manager', 'workspace_admin'])->count()
            + ($workspace->primaryManager ? 1 : 0);
        $talentCount = $activeMembers->where('role', 'talent')->count()
            + ($workspace->primaryTalent ? 1 : 0);
        $clientCount = $activeMembers->whereIn('role', ['client_admin', 'client_staff', 'client'])->count();
        $pendingInviteCount = $invitations->where('status', 'pending')->count();
    @endphp

    {{-- ── Page header ──────────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-3">
            <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
            <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
            <span>Members</span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-primary">Workspace Members</h1>
                <p class="text-[12px] text-outline mt-1">
                    {{ $workspace->name }} &middot; {{ $workspace->workspace_code }}
                </p>
            </div>
            <div class="flex items-center gap-2 mt-1">
                @if ($canInvite)
                    <a href="{{ route('workspace.members.invite', $workspace) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white hover:brightness-110 transition-all"
                       style="background:#0058be;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">person_add</span>
                        Invite
                    </a>
                @endif
                <a href="{{ route('workspace.show', $workspace) }}"
                   class="inline-flex items-center gap-1.5 text-sm text-secondary hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                    Workspace
                </a>
            </div>
        </div>
    </div>

    {{-- ── Flash ────────────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <x-portal.alert type="success" class="mb-5">{{ session('success') }}</x-portal.alert>
    @endif
    @if ($errors->any())
        <x-portal.alert type="error" class="mb-5">
            <p class="font-semibold mb-1">Please check the member form.</p>
            <ul class="text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-portal.alert>
    @endif

    {{-- ── Stat cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <x-portal.stat-card
            label="Active Members"
            :value="$activeMembers->count()"
            icon="group"
            accent="secondary" />
        <x-portal.stat-card
            label="Managers"
            :value="$managerCount"
            icon="manage_accounts"
            accent="secondary" />
        <x-portal.stat-card
            label="Talent"
            :value="$talentCount"
            icon="work"
            accent="secondary" />
        <x-portal.stat-card
            label="Client Team"
            :value="$clientCount"
            icon="business"
            accent="secondary"
            :hint="$pendingInviteCount > 0 ? $pendingInviteCount . ' invite' . ($pendingInviteCount !== 1 ? 's' : '') . ' pending' : null"
            :hint-class="$pendingInviteCount > 0 ? 'text-status-payment-due' : 'text-outline'" />
    </div>

    {{-- ── Add existing user ───────────────────────────────────────────────── --}}
    @if ($canManageMembers)
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden mb-5">
            <div class="px-6 py-4 border-b border-border-subtle flex items-center gap-2"
                 style="background:rgba(247,249,251,1);">
                <span class="material-symbols-outlined text-secondary" style="font-size: 16px;">group_add</span>
                <h3 class="text-sm font-bold text-on-surface">Add Existing User</h3>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('workspace.members.store', $workspace) }}" class="grid grid-cols-1 lg:grid-cols-[1fr_220px_auto] gap-3">
                    @csrf
                    <select name="user_id" required class="rounded-lg border-border-subtle text-sm focus:border-secondary focus:ring-secondary/20 bg-white">
                        <option value="">Select user</option>
                        @foreach ($availableUsers as $availableUser)
                            <option value="{{ $availableUser->id }}" @selected(old('user_id') == $availableUser->id)>
                                {{ $availableUser->name }} ({{ $availableUser->email }})
                            </option>
                        @endforeach
                    </select>
                    <select name="workspace_role" required class="rounded-lg border-border-subtle text-sm focus:border-secondary focus:ring-secondary/20 bg-white">
                        @foreach ($allowedRoles as $allowedRole)
                            <option value="{{ $allowedRole }}" @selected(old('workspace_role') === $allowedRole)>
                                {{ $roleLabels[$allowedRole] ?? ucfirst(str_replace('_', ' ', $allowedRole)) }}
                            </option>
                        @endforeach
                    </select>
                    <button class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white hover:brightness-110 transition-all"
                            style="background:#0058be;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                        Add
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- ── Active team table ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden mb-5">
        <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between"
             style="background:rgba(247,249,251,1);">
            <h3 class="text-sm font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">people</span>
                Active Team
            </h3>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                  style="background:rgba(0,88,190,0.08);color:#0058be;">{{ $activeMembers->count() }} active</span>
        </div>

        @if ($activeMembers->isEmpty())
            <div class="p-10 text-center">
                <span class="material-symbols-outlined text-outline block mb-2" style="font-size:28px;">people</span>
                <p class="text-sm text-outline">No active members yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0;">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-outline uppercase tracking-wider">Member</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-outline uppercase tracking-wider">Platform Role</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-outline uppercase tracking-wider">Workspace Role</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-outline uppercase tracking-wider">Added</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-outline uppercase tracking-wider">Activity</th>
                            @if ($canManageMembers)
                                <th class="text-right px-6 py-3 text-xs font-semibold text-outline uppercase tracking-wider">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @foreach ($activeMembers as $member)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                             style="background:#0058be;">
                                            {{ strtoupper(substr($member->user->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-on-surface text-sm">{{ $member->user->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-outline">{{ $member->user->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-on-surface-variant">
                                    {{ $member->user?->getRoleNames()->map(fn ($r) => ucwords(str_replace('_', ' ', $r)))->implode(', ') ?: 'No platform role' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold"
                                          style="background:rgba(0,88,190,0.08);color:#0058be;border:1px solid rgba(0,88,190,0.15);">
                                        {{ $member->roleLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-on-surface-variant">{{ $member->joined_at?->format('d M Y') ?? 'Not recorded' }}</td>
                                <td class="px-6 py-4 text-xs text-on-surface-variant">{{ $member->user?->updated_at?->diffForHumans() ?? 'Not available' }}</td>
                                @if ($canManageMembers)
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('workspace.members.update', [$workspace, $member]) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <select name="workspace_role" class="rounded-lg border-border-subtle text-xs bg-white focus:border-secondary focus:ring-secondary/20">
                                                    @foreach ($allowedRoles as $allowedRole)
                                                        <option value="{{ $allowedRole }}" @selected($member->role === $allowedRole)>
                                                            {{ $roleLabels[$allowedRole] ?? ucfirst(str_replace('_', ' ', $allowedRole)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/20 transition-all">
                                                    Update
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('workspace.members.deactivate', [$workspace, $member]) }}" onsubmit="return confirm('Deactivate this workspace member?');">
                                                @csrf
                                                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-status-blocked/20 text-status-blocked hover:bg-status-blocked/10 transition-all">
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ── Invitations ──────────────────────────────────────────────────────── --}}
    @if ($canInvite)
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between"
                 style="background:rgba(247,249,251,1);">
                <h3 class="text-sm font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">outgoing_mail</span>
                    Invitations
                </h3>
                @if ($pendingInviteCount > 0)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                          style="background:rgba(245,158,11,0.10);color:#D97706;">
                        {{ $pendingInviteCount }} pending
                    </span>
                @else
                    <span class="text-[11px] text-outline">No pending invitations</span>
                @endif
            </div>
            @if ($invitations->isEmpty())
                <div class="p-10 text-center">
                    <span class="material-symbols-outlined text-outline block mb-2" style="font-size:28px;">outgoing_mail</span>
                    <p class="text-sm text-outline">No invitations have been created for this workspace.</p>
                </div>
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($invitations as $invitation)
                        @php
                            $invStatusCls = match($invitation->status) {
                                'pending'           => 'bg-status-payment-due/10 text-status-payment-due border-status-payment-due/20',
                                'accepted'          => 'bg-status-active/10 text-status-active border-status-active/20',
                                'revoked', 'expired'=> 'bg-status-blocked/10 text-status-blocked border-status-blocked/20',
                                default             => 'bg-surface-container-low text-outline border-border-subtle',
                            };
                        @endphp
                        <div class="px-6 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between hover:bg-surface-container-low transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                     style="background:#94A3B8;">
                                    {{ strtoupper(substr($invitation->name ?: $invitation->email, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-on-surface text-sm">{{ $invitation->name ?: $invitation->email }}</p>
                                    <p class="text-xs text-outline mt-0.5">{{ $invitation->email }} &middot; {{ $roleLabels[$invitation->workspace_role] ?? $invitation->workspace_role }}</p>
                                    <p class="text-xs text-outline mt-0.5">
                                        Invited by {{ $invitation->inviter?->name ?? 'System' }} &middot; {{ $invitation->created_at->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $invStatusCls }}">
                                    {{ \App\Models\WorkspaceInvitation::statusLabels()[$invitation->status] ?? ucfirst($invitation->status) }}
                                </span>
                                @if ($invitation->status === 'pending')
                                    <form method="POST" action="{{ route('workspace.members.invitations.resend', [$workspace, $invitation]) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/20 transition-all">Resend</button>
                                    </form>
                                    <form method="POST" action="{{ route('workspace.members.invitations.revoke', [$workspace, $invitation]) }}" onsubmit="return confirm('Revoke this invitation?');">
                                        @csrf
                                        <button class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-status-blocked/20 text-status-blocked hover:bg-status-blocked/10 transition-all">Revoke</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

</x-layouts.gvos>
